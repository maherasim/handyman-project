<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProviderPayout;
use App\Models\HandymanPayout;
use App\Traits\EarningTrait;
use App\Models\BookingHandymanMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Wallet;
use App\Models\User;
use App\Models\PaymentHistory;
use App\Models\PaymentGateway;
use App\Http\Resources\API\PaymentResource;
use App\Http\Resources\API\PaymentHistoryResource;
use App\Http\Resources\API\GetCashPaymentHistoryResource;
use App\Traits\NotificationTrait;
use App\Http\Resources\API\PaymentGatewayResource;
use App\Models\Service;
use App\Models\Setting;
use DB;
use App\Models\CommissionEarning;
use Illuminate\Support\Facades\Mail;
use App\Mail\BankTransferPaymentNotificationMail;

class PaymentController extends Controller
{
    use NotificationTrait;
    use EarningTrait;


    public function savePayment(Request $request)
    {
        $data = $request->all();
        $data['datetime'] = isset($request->datetime) ? date('Y-m-d H:i:s', strtotime($request->datetime)) : date('Y-m-d H:i:s');

        $booking = Booking::find($request->booking_id);
        $result = Payment::create($data);

        if (!$booking || !$result) {
            return comman_message_response(__('messages.booking_not_found'), 404);
        }

        $isAdvance = $result->payment_status == 'advanced_paid';
        $isRemaining = $result->payment_status == 'paid';

        $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
        $admin_user_id = User::where('user_type', 'admin')->value('id');

        if ($isAdvance) {
            $advance_paid_amount = $request->advance_paid_amount;
            $booking->advance_paid_amount = $advance_paid_amount;
            $booking->update();

            $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
            $provider_earning = $advance_paid_amount - $admin_commission_amount;

            // Credit only admin on advance; hold provider share until final payment to avoid later clawbacks
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid',
            ]);
        }

        if ($isRemaining) {
            $booking->status = 'completed';
            $booking->update();

            $advance_paid = $booking->advance_paid_amount ?? 0;
            $total_amount = $booking->total_amount;
            $remaining_amount = $total_amount - $advance_paid;
            $result->total_amount = $remaining_amount;
            $result->save();

            // Compute totals across the whole booking
            $total_admin_commission = ($total_amount * $admin_commission_percentage) / 100;
            $provider_total_earning = $total_amount - $total_admin_commission;
            $remaining_admin_commission = ($remaining_amount > 0)
                ? ($remaining_amount * $admin_commission_percentage) / 100
                : 0;

            // Calculate handyman payouts from provider_total_earning
            $handymen = BookingHandymanMapping::where('booking_id', $booking->id)->pluck('handyman_id');
            $handyman_payouts = [];
            $total_handyman_share = 0;
            foreach ($handymen as $handyman_id) {
                $handyman = User::find($handyman_id);
                if (!$handyman || $handyman->handyman_commission === null) {
                    continue;
                }
                // Clamp each handyman commission between 1% and 85%
                $commission_percent = max(1, min(85, $handyman->handyman_commission));
                $handyman_total_share = ($provider_total_earning * $commission_percent) / 100;
                $total_handyman_share += $handyman_total_share;
                $handyman_payouts[] = [
                    'handyman_id' => $handyman_id,
                    'amount' => $handyman_total_share,
                ];
            }

            // Final provider earning = provider_total_earning - sum(handymen)
            $provider_final_earning = $provider_total_earning - $total_handyman_share;
            if ($provider_final_earning < 0) {
                // Guard rail: never pay negative. If business rules allow, you can scale down handymen instead.
                $provider_final_earning = 0;
            }

            // Pay handymen now
            foreach ($handyman_payouts as $payout) {
                Wallet::firstOrCreate(['user_id' => $payout['handyman_id']])->increment('amount', $payout['amount']);

                HandymanPayout::create([
                    'handyman_id' => $payout['handyman_id'],
                    'booking_id' => $booking->id,
                    'amount' => $payout['amount'],
                    'status' => 'paid',
                    'paid_date' => Carbon::now(),
                    'payment_method' => 'wallet',
                    'payment_gateway' => 'wallet',
                ]);

                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'handyman',
                    'employee_id' => $payout['handyman_id'],
                    'commission_amount' => $payout['amount'],
                    'commission_status' => 'paid',
                ]);
            }

            // Pay remaining admin commission only for the remaining payment now
            if ($remaining_admin_commission > 0) {
                Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $remaining_admin_commission);

                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'admin',
                    'employee_id' => $admin_user_id,
                    'commission_amount' => $remaining_admin_commission,
                    'commission_status' => 'paid',
                ]);
            }

            // Pay provider the final net amount once (advance was held)
            Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_final_earning);

            ProviderPayout::create([
                'provider_id' => $booking->provider_id,
                'amount' => $provider_final_earning,
                'payment_method' => 'wallet',
                'paid_date' => Carbon::now(),
                'status' => 'paid',
                'booking_id' => $booking->id,
                'payment_gateway' => 'wallet',
            ]);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'provider',
                'employee_id' => $booking->provider_id,
                'commission_amount' => $provider_final_earning,
                'commission_status' => 'paid',
            ]);

            // Mark all commissions as paid (keeps previous admin advance record consistent)
            CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
        }

        // ALWAYS create new PaymentHistory entry - NO CONDITIONS
        $payment_history = [
            'payment_id' => $result->id,
            'booking_id' => $result->booking_id,
            'parent_id' => $result->booking_id,
            'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
            'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
            'sender_id' => $request->customer_id,
            'receiver_id' => $booking->provider_id, // Always use provider_id
            'datetime' => $request->datetime,
            'total_amount' => $request->total_amount,
            'txn_id' => $request->txn_id,
            'type' => $request->payment_type,
            'text' => __('messages.payment_transfer', [
                'from' => get_user_name($request->customer_id),
                'to' => get_user_name($booking->provider_id),
                'amount' => getPriceFormat((float)$request->total_amount),
            ]),
        ];

        $res = PaymentHistory::create($payment_history);
        $res->parent_id = $res->id;
        $res->save();

        // Assign payment ID to booking
        $booking->payment_id = $result->id;
        $booking->update();

        // Deduct from customer wallet if used
        if ($request->payment_type == 'wallet') {
            $wallet = Wallet::where('user_id', $booking->customer_id)->first();
            if ($wallet && $wallet->amount >= $request->total_amount) {
                $wallet->amount -= $request->total_amount;
                $wallet->save();

                $service = Service::find($booking->service_id);
                $this->sendNotification([
                    'activity_type' => 'paid_with_wallet',
                    'wallet' => $wallet,
                    'booking_id' => $request->booking_id,
                    'booking_amount' => $request->total_amount,
                    'service_name' => $service->name ?? '',
                ]);
            } else {
                return comman_message_response(__('messages.wallent_balance_error'), 400);
            }
        }

        // Send payment status notification
        $this->sendNotification([
            'activity_type' => 'payment_message_status',
            'payment_status' => $data['payment_status'],
            'booking_id' => $booking->id,
            'booking' => $booking,
            'booking_amount' => $request->total_amount,
        ]);

        if ($result->payment_status == 'failed') {
            return comman_message_response(__('messages.payment_failed'), 400);
        }

        return comman_message_response(__('messages.payment_completed'), 200);
    }


    public function getpaymentall(Request $request)
    {
        $query = Payment::query()
            ->with(['booking.service', 'booking.bookingPackage', 'customer']) // Eager load customer
            ->myPayment()
            ->where(function ($q) {
                $q->where('payment_type', '!=', 'bank_transfer')
                    ->orWhere(function ($sub) {
                        $sub->where('payment_type', 'bank_transfer')
                            ->where('status', 1);
                    });
            });

        // Apply filters
        $filter = $request->filter;
        if (isset($filter['column_status'])) {
            $query->where('payment_status', $filter['column_status']);
        }

        // Admin check (not strictly needed here)
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery(); // optional
        }

        // Get paginated results
        $payments = $query->paginate(10);

        // Transform each payment entry
        $payments->getCollection()->transform(function ($payment) {
            $booking = $payment->booking;

            // Replace booking_id with service or service package name
            if ($booking) {
                if (!empty($booking->bookingPackage)) {
                    $payment->booking_id = optional($booking->bookingPackage)->name . " (" . __('messages.service_package') . ")";
                } else {
                    $payment->booking_id = optional($booking->service)->name . " (" . __('messages.service') . ")";
                }
            } else {
                $payment->booking_id = '-';
            }

            // Replace customer_id with customer name
            $payment->customer_id = optional($payment->customer)->name ?? '-';

            return $payment;
        });

        return response()->json([
            'status' => true,
            'data' => $payments
        ]);
    }




    public function saveBankTransferPayment(Request $request)
    {
        $data = $request->all();
        $data['datetime'] = isset($request->datetime)
            ? date('Y-m-d H:i:s', strtotime($request->datetime))
            : date('Y-m-d H:i:s');
    
        // Always pending for bank transfers until admin verifies
        $data['status'] = 0;
        $data['payment_status'] = 'pending_by_admin';
        $data['payment_method'] = 'bank_transfer';
        $data['payment_gateway'] = 'bank_transfer';
    
        $payment = Payment::create($data);
        $booking = Booking::with(['customer', 'provider'])->find($request->booking_id);
    
        if (!$booking || !$payment) {
            return comman_message_response(__('messages.booking_not_found'), 404);
        }
    
        $isAdvance = $request->type === 'advance_payment';       
        $isRemaining = !$isAdvance; // anything else is remaining
    
        $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
        $admin_user_id = User::where('user_type', 'admin')->value('id');
    
        // ---------------------------
        // ✅ Advance payment
        // ---------------------------
        if ($isAdvance) {
            $advance_paid_amount = $request->total_amount;
            $booking->advance_paid_amount = $advance_paid_amount;
            $booking->update();
    
            $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
    
            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'pending',
            ]);
        }
    
        // ---------------------------
        // ✅ Remaining payment
        // ---------------------------
        if ($isRemaining) {
            $advance_paid = $booking->advance_paid_amount ?? 0;
            $total_amount = $booking->total_amount;
            $remaining_amount = $total_amount - $advance_paid;
    
            $payment->total_amount = $remaining_amount;
            $payment->save();
    
            $total_admin_commission = ($total_amount * $admin_commission_percentage) / 100;
            $provider_total_earning = $total_amount - $total_admin_commission;
    
            $handymen = BookingHandymanMapping::where('booking_id', $booking->id)->pluck('handyman_id');
            $handyman_payouts = [];
            $total_handyman_share = 0;
    
            foreach ($handymen as $handyman_id) {
                $handyman = User::find($handyman_id);
                if (!$handyman || $handyman->handyman_commission === null) continue;
    
                $commission_percent = max(1, min(85, $handyman->handyman_commission));
                $handyman_share = ($provider_total_earning * $commission_percent) / 100;
                $total_handyman_share += $handyman_share;
    
                $handyman_payouts[] = [
                    'handyman_id' => $handyman_id,
                    'amount' => $handyman_share,
                ];
            }
    
            $provider_final_earning = $provider_total_earning - $total_handyman_share;
            if ($provider_final_earning < 0) $provider_final_earning = 0;
    
            // ✅ Create pending handyman payouts
            foreach ($handyman_payouts as $payout) {
                HandymanPayout::create([
                    'handyman_id' => $payout['handyman_id'],
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'amount' => $payout['amount'],
                    'status' => 'pending',
                    'payment_method' => 'bank_transfer',
                    'payment_gateway' => 'bank_transfer',
                ]);
    
                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'handyman',
                    'employee_id' => $payout['handyman_id'],
                    'commission_amount' => $payout['amount'],
                    'commission_status' => 'pending',
                ]);
            }
    
            // ✅ Create pending provider payout
            ProviderPayout::create([
                'provider_id' => $booking->provider_id,
                'payment_id' => $payment->id,
                'amount' => $provider_final_earning,
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'payment_gateway' => 'bank_transfer',
                'booking_id' => $booking->id,
            ]);
    
            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'provider',
                'employee_id' => $booking->provider_id,
                'commission_amount' => $provider_final_earning,
                'commission_status' => 'pending',
            ]);
    
            // ✅ Admin commission for remaining
            $remaining_admin_commission = ($remaining_amount * $admin_commission_percentage) / 100;
            if ($remaining_admin_commission > 0) {
                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'admin',
                    'employee_id' => $admin_user_id,
                    'commission_amount' => $remaining_admin_commission,
                    'commission_status' => 'pending',
                ]);
            }
        }
    
        // ---------------------------
        // ✅ Always create Payment History
        // ---------------------------
        $payment_history = [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'parent_id' => $payment->booking_id,
            'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
            'status' => 'pending_by_admin',
            'sender_id' => $request->customer_id,
            'receiver_id' => $booking->provider_id,
            'datetime' => $request->datetime,
            'total_amount' => $request->total_amount,
            'txn_id' => $request->txn_id,
            'type' => $request->type,
            'text' => __('messages.payment_transfer', [
                'from' => get_user_name($request->customer_id),
                'to' => get_user_name($booking->provider_id),
                'amount' => getPriceFormat((float)$request->total_amount),
            ]),
        ];
    
        $history = PaymentHistory::create($payment_history);
        $history->parent_id = $history->id;
        $history->save();
    
        $booking->payment_id = $payment->id;
        $booking->update();
    
        // ✅ Notification
        $this->sendNotification([
            'activity_type' => 'payment_message_status',
            'payment_status' => 'pending_by_admin',
            'booking_id' => $booking->id,
            'booking_amount' => $request->total_amount,
            'booking' => $booking,
        ]);
    
        // ✅ Send email notification to admin
        try {
            $adminEmail = 'asimriazasim107@gmail.com';
            Mail::to($adminEmail)->send(new BankTransferPaymentNotificationMail($payment, $booking, $request->type));
        } catch (\Exception $e) {
            // Log error but don't fail the payment creation
            \Log::error('Failed to send bank transfer payment notification email: ' . $e->getMessage());
        }
    
        return comman_message_response(__('messages.payment_pending_admin_approval'), 200);
    }
    




    public function paymentList(Request $request)
    {
        $payment = Payment::myPayment()->with('booking');
        if ($request->has('booking_id') && !empty($request->booking_id)) {
            $payment->where('booking_id', $request->booking_id);
        }
        if ($request->has('payment_type') && !empty($request->payment_type)) {

            if ($request->payment_type == 'cash') {
                $payment->where('payment_type', $request->payment_type);
            }
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id', 'desc')->paginate($per_page);
        $items = PaymentResource::collection($payment);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function transferPayment(Request $request)
    {
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $admin = json_decode($sitesetup->value);
        $data = $request->all();
        $auth_user = authSession();
        $user_id = $auth_user->id;


        date_default_timezone_set($admin->time_zone ?? 'UTC');
        $data['datetime'] = date('Y-m-d H:i:s');

        if ($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.HANDYMAN_SEND_PROVIDER')) {
            $data['text'] = __(
                'messages.payment_transfer',
                ['from' => get_user_name($data['sender_id']), 'to' => get_user_name($data['receiver_id']), 'amount' => getPriceFormat((float)$data['total_amount'])]
            );
        }
        if ($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.PROVIDER_APPROVED_CASH')) {
            $data['text'] = __('messages.cash_approved', ['amount' => getPriceFormat((float)$data['total_amount']), 'name' => get_user_name($data['receiver_id'])]);
        }
        if ($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.PROVIDER_SEND_ADMIN')) {
            $data['text'] =  __('messages.payment_transfer', [
                'from' => get_user_name($data['sender_id']),
                'to' => get_user_name(admin_id()),
                'amount' => getPriceFormat((float)$data['total_amount'])
            ]);
        }
        $result = \App\Models\PaymentHistory::create($data);

        if ($data['action'] == 'provider_approved_cash' && $data['status'] == 'approved_by_provider') {

            $bookingdata = Booking::find($request->booking_id);
            $paymentdata = Payment::where('booking_id', $bookingdata->id)->first();
            if ($bookingdata->payment_id != null) {
                $payment_status = 'pending_by_admin';
                $paymentdata->update(['payment_status' => $payment_status]);
            }
        }
        $message = trans('messages.transfer');
        if ($request->is('api/*')) {
            return comman_message_response($message);
        }
    }

    public function paymentHistory(Request $request)
    {
        $booking_id = $request->booking_id;
        $payment = PaymentHistory::where('booking_id', $booking_id);

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id', 'desc')->paginate($per_page);
        $items = PaymentHistoryResource::collection($payment);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function getCashPaymentHistory(Request $request)
    {
        $payment_id = $request->payment_id;
        $payment = PaymentHistory::where('payment_id', $payment_id)->with('booking');

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id', 'desc')->paginate($per_page);
        $items = GetCashPaymentHistoryResource::collection($payment);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }


    public function paymentDetail(Request $request)
    {
        $auth_user = authSession();
        $user_id = $auth_user->id;

        $get_all_payments = PaymentHistory::query();
        if (!empty($request->status)) {
            $get_all_payments = $get_all_payments->where('status', $request->status);
        }

        $user = auth()->user();
        $role = $user->hasAnyRole(['handyman', 'provider']) ? $user->getRoleNames()->first() : null;
        $status = $request->status ?? null;

        $roleActionMap = [
            'handyman' => [
                'approved_by_handyman' => ['action' => 'handyman_approved_cash', 'column' => 'receiver_id'],
                'pending_by_provider'  => ['action' => 'handyman_send_provider', 'column' => 'sender_id'],
                'approved_by_provider' => ['action' => 'provider_approved_cash', 'column' => 'sender_id'],
                'default'              => ['actions' => ['handyman_approved_cash', 'handyman_send_provider', 'provider_approved_cash', 'admin_approved_cash', 'provider_send_admin']],
            ],
            'provider' => [
                'pending_by_admin'     => ['action' => 'provider_send_admin', 'column' => 'sender_id'],
                'approved_by_provider' => ['action' => 'provider_approved_cash', 'column' => 'receiver_id'],
                'pending_by_provider'  => ['action' => 'handyman_send_provider', 'column' => 'receiver_id'],
                'approved_by_admin'    => ['action' => 'admin_approved_cash', 'column' => 'sender_id'],
                'default'              => ['actions' => ['handyman_send_provider', 'provider_approved_cash', 'provider_send_admin', 'admin_approved_cash']],
            ],
        ];

        // Check if the user has either handyman or provider role
        if ($role && isset($roleActionMap[$role])) {
            if (!empty($status) && isset($roleActionMap[$role][$status])) {
                $actionData = $roleActionMap[$role][$status];
                $get_all_payments = $get_all_payments->where('action', $actionData['action'])
                    ->where($actionData['column'], $user_id)
                    ->orderBy('id', 'desc');
            } else {
                // Apply the default case for both roles
                $get_all_payments = $get_all_payments->whereIn('action', $roleActionMap[$role]['default']['actions'])
                    ->where(function ($query) use ($user_id) {
                        $query->where('receiver_id', $user_id)
                            ->orWhere('sender_id', $user_id);
                    })
                    ->orderBy('id', 'desc')
                    ->groupBy('booking_id');
            }
        }


        $get_all_payments = $get_all_payments->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('payment_histories')
                ->groupBy('booking_id');
        });


        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $get_all_payments->count();
            }
        }


        // apply date filter
        if (!empty($request->from) && !empty($request->to)) {
            $get_all_payments = $get_all_payments->whereDate('datetime', '>=', $request->from)->whereDate('datetime', '<=',  $request->to);
        }

        $get_all_payments = $get_all_payments->paginate($per_page);
        $items = PaymentHistoryResource::collection($get_all_payments);

        $response = [
            'today_cash' => (float)$get_all_payments->sum('total_amount'),
            'total_cash_in_hand' => (float)total_cash_in_hand($user_id),
            'cash_detail' => $items,
        ];

        return comman_custom_response($response);
    }

    public function getCashPayment(Request $request)
    {
        $payment = Payment::where('payment_type', 'cash');

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id', 'desc')->paginate($per_page);
        $items = PaymentResource::collection($payment);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function paymentGateways(Request $request)
    {
        $payment = PaymentGateway::where('status', 1)->where('type', '!=', 'razorPayX')->get();
        if ($request->has('is_add_wallet') && $request->is_add_wallet == true) {
            $walletEntry = new \stdClass();
            $walletEntry->id = null; // Or assign a unique identifier if needed
            $walletEntry->title = 'Wallet';
            $walletEntry->type = 'wallet';
            $walletEntry->status = 1; // Active by default
            $walletEntry->is_test = 0;
            $walletEntry->value = null;
            $walletEntry->live_value = null;

            // Use prepend directly on the collection
            $payment->prepend($walletEntry);
        }
        $payment = PaymentGatewayResource::collection($payment);

        return comman_custom_response($payment);
    }

    /**
     * Get handyman earnings/payments list
     * Returns payment history with handyman commission earnings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handymanEarningsList(Request $request)
    {
        // Check if user is handyman
        $user = auth()->user();
        if (!$user->hasRole('handyman')) {
            return comman_message_response(__('messages.unauthorized'), 403);
        }

        $query = Payment::query()
            ->myPayment()
            ->with([
                'handymanEarning' => function ($q) {
                    $q->where('user_type', 'handyman')
                      ->where('commission_status', 'paid');
                },
                'booking.service',
                'booking.bookingPackage',
                'customer',
                'postJobRequest'
            ])
            ->where(function ($q) {
                $q->where('payment_type', '!=', 'bank_transfer')
                    ->orWhere(function ($sub) {
                        $sub->where('payment_type', 'bank_transfer')->where('status', 1);
                    });
            })
            ->groupBy('payments.booking_id');

        // Apply filters
        $filter = $request->filter;
        if (isset($filter) && isset($filter['column_status'])) {
            $query->where('payment_status', $filter['column_status']);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('booking.service', function ($serviceQuery) use ($search) {
                    $serviceQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('display_name', 'like', '%' . $search . '%')
                                  ->orWhere('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('postJobRequest', function ($postJobQuery) use ($search) {
                    $postJobQuery->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        // Pagination
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $query->count();
            }
        }

        // Ordering
        $orderBy = $request->get('order_by', 'datetime');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $payments = $query->paginate($per_page);

        // Transform data
        $transformedData = $payments->getCollection()->map(function ($payment) {
            $booking = $payment->booking;
            $customer = $payment->customer;
            $handymanEarning = $payment->handymanEarning;
            
            // Get service/post job name
            $serviceName = '-';
            if ($booking) {
                if (!empty($booking->bookingPackage)) {
                    $serviceName = optional($booking->bookingPackage)->name . " (Service Package)";
                } elseif ($booking->service) {
                    $serviceName = optional($booking->service)->name . " (Service)";
                }
            } elseif ($payment->postJobRequest) {
                $serviceName = optional($payment->postJobRequest)->title . " (Post Job Request)";
            }

            // Get user info
            $userInfo = [
                'id' => $customer->id ?? null,
                'name' => $customer->display_name ?? ($customer ? ($customer->first_name . ' ' . $customer->last_name) : '-'),
                'profile_image' => getSingleMedia($customer, 'profile_image', null),
                'address' => $customer->address ?? null,
            ];

            // Format date
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
            $dateFormat = $datetime->date_format ?? 'd F, Y';
            $timeFormat = $datetime->time_format ?? 'H:i';
            $formattedDate = date("$dateFormat $timeFormat", strtotime($payment->datetime));

            return [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id ? '#' . $payment->booking_id : null,
                'service_post_job' => $serviceName,
                'user' => $userInfo,
                'payment_type' => $payment->payment_type ?? '-',
                'payment_status' => $payment->payment_status ?? '-',
                'status_label' => $payment->payment_status ? str_replace('_', ' ', ucfirst($payment->payment_status)) : '-',
                'datetime' => $formattedDate,
                'datetime_raw' => $payment->datetime,
                'my_earning' => $handymanEarning ? (float)$handymanEarning->commission_amount : 0,
                'my_earning_formatted' => $handymanEarning ? getPriceFormat($handymanEarning->commission_amount) : getPriceFormat(0),
                'total_amount' => (float)$payment->total_amount,
                'total_amount_formatted' => getPriceFormat($payment->total_amount),
            ];
        });

        $response = [
            'pagination' => [
                'total_items' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'total_pages' => $payments->lastPage(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
                'next_page_url' => $payments->nextPageUrl(),
                'previous_page_url' => $payments->previousPageUrl(),
            ],
            'data' => $transformedData,
        ];

        return comman_custom_response($response);
    }
}
