<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProviderPayout;
use App\Traits\EarningTrait;
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

class PaymentController extends Controller
{
    use NotificationTrait;
    use EarningTrait;

    // public function savePayment(Request $request)
    // {
    //     $data = $request->all();
    //     $data['datetime'] = isset($request->datetime) ? date('Y-m-d H:i:s',strtotime($request->datetime)) : date('Y-m-d H:i:s');
    //     $result = Payment::create($data);
    //     $booking = Booking::find($request->booking_id);
    //     if(!empty($result) && $result->payment_status == 'advanced_paid'){
    //         $booking->advance_paid_amount  = $request->advance_paid_amount;
    //         $booking->status  = 'pending';

    //         $booking->update();

    //         // ✅ Manually split advance payment commission here
    //         $advance_paid_amount = $request->advance_paid_amount;

    //         // Example: get commission percentage from settings
    //         $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;

    //         // Calculate
    //         $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
    //         $provider_earning = $advance_paid_amount - $admin_commission_amount;

    //         // Add provider earning to wallet
    //         $provider_wallet = Wallet::where('user_id', $booking->provider_id)->first();
    //         if ($provider_wallet) {
    //             $provider_wallet->amount += $provider_earning;
    //             $provider_wallet->update();
    //         }

    //         // Add admin commission to admin wallet
    //         $admin_user_id = User::where('user_type', 'admin')->value('id');
    //         $admin_wallet = Wallet::where('user_id', $admin_user_id)->first();
    //         if ($admin_wallet) {
    //             $admin_wallet->amount += $admin_commission_amount;
    //             $admin_wallet->update();
    //         }

    //         // Optionally record it inside CommissionEarning table (separate record if you want)
    //         CommissionEarning::create([
    //             'booking_id' => $booking->id,
    //             'user_type' => 'admin',
    //             'employee_id' => $admin_user_id,
    //             'commission_amount' => $admin_commission_amount,
    //             'commission_status' => 'paid', // or 'paid' if you want
    //         ]);

    //         CommissionEarning::create([
    //             'booking_id' => $booking->id,
    //             'user_type' => 'provider',
    //             'employee_id' => $booking->provider_id,
    //             'commission_amount' => $booking->total_amount - $provider_earning,
    //             'commission_status' => 'unpaid',
    //         ]);

    //         ProviderPayout::create([
    //             'provider_id' => $booking->provider_id,
    //             'amount' => $provider_earning, // Only provider's share of advance payment
    //             'payment_method' => 'wallet', // Payment done into wallet
    //             'paid_date' => Carbon::now(), // Current timestamp
    //             'status' => 'pending', // Payout not sent yet (only earned)
    //             'booking_id' => $booking->id, // Optional, if your table has booking_id field
    //             'payment_gateway' => 'wallet', // Optional, if your table has this
    //         ]);

    //     }

    //     $firstHandymanId = optional($booking->handymanAdded->first())->handyman_id;
    //     $assignedUserData = User::find($firstHandymanId);
    //     if($firstHandymanId != null && $assignedUserData->user_type == 'provider'){
    //         $payment_history = [
    //             'payment_id' => $result->id,
    //             'booking_id' => $result->booking_id,
    //             'parent_id' => $result->booking_id,
    //             'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
    //             'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
    //             'sender_id' => $request->customer_id,
    //             'receiver_id' => $firstHandymanId,
    //             'datetime' => $request->datetime,
    //             'total_amount' => $request->total_amount,
    //             'txn_id' => $request->txn_id,
    //             'type' => $request->payment_type,
    //             'text'     =>    __('messages.payment_transfer',['from' => get_user_name( $request->customer_id),'to' => get_user_name($firstHandymanId),'amount' => getPriceFormat((float)$request->total_amount) ]),
    //         ];
    //         $res =  PaymentHistory::create($payment_history);
    //         $res->parent_id = $res->id;
    //         $res->update();
    //     }
    //     $service_id = Booking::where('id',$request->booking_id)->pluck('service_id');
    //     $service = Service::where('id',$service_id)->first();
    //     $booking->payment_id = $result->id;
    //     $booking->update();

    //     if($booking->status == 'completed' && $result->payment_status=='paid'){
    //         CommissionEarning::where('booking_id',$booking->id)->update(['commission_status'=>'unpaid']);
    //     }
    //     $status_code = 200;
    //     if($request->payment_type == 'wallet'){
    //         $wallet = Wallet::where('user_id',$booking->customer_id)->first();
    //         if($wallet !== null){
    //             $wallet_amount = $wallet->amount;
    //             if($wallet_amount >= $request->total_amount){
    //                 $wallet->amount = $wallet->amount - $request->total_amount;
    //                 $wallet->update();
    //                 $activity_data = [
    //                     'activity_type' => 'paid_with_wallet',
    //                     'wallet' => $wallet,
    //                     'booking_id'=>$request->booking_id,
    //                     'booking_amount'=>$request->total_amount,
    //                     'service_name' => $service->name,
    //                 ];
    //                 $this->sendNotification($activity_data);

    //             }else{
    //                 $message = __('messages.wallent_balance_error');
    //             }
    //         }
    //     }
    //     $message = __('messages.payment_completed');
    //     $activity_data = [
    //         'activity_type' => 'payment_message_status',
    //         'payment_status'=>  $data['payment_status'],
    //         'booking_id' => $booking->id,
    //         'booking' => $booking,
    //         'booking_amount' => $request->total_amount,
    //     ];
    //     $this->sendNotification($activity_data);

    //     if($result->payment_status == 'failed')
    //     {
    //         $status_code = 400;
    //     }
    //     return comman_message_response($message,$status_code);
    // }
 public function savePayment(Request $request)
{
    $data = $request->all();
    $data['datetime'] = isset($request->datetime) ? date('Y-m-d H:i:s', strtotime($request->datetime)) : date('Y-m-d H:i:s');

    $result = Payment::create($data);
    $booking = Booking::find($request->booking_id);

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
        $booking->status = 'pending';
        $booking->update();

        $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
        $provider_earning = $advance_paid_amount - $admin_commission_amount;

        Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_earning);
        Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

        CommissionEarning::create([
            'booking_id' => $booking->id,
            'user_type' => 'admin',
            'employee_id' => $admin_user_id,
            'commission_amount' => $admin_commission_amount,
            'commission_status' => 'paid',
        ]);

        CommissionEarning::create([
            'booking_id' => $booking->id,
            'user_type' => 'provider',
            'employee_id' => $booking->provider_id,
            'commission_amount' => $booking->total_amount - $provider_earning,
            'commission_status' => 'unpaid',
        ]);

        ProviderPayout::create([
            'provider_id' => $booking->provider_id,
            'amount' => $provider_earning,
            'payment_method' => 'wallet',
            'paid_date' => Carbon::now(),
            'status' => 'paid',
            'booking_id' => $booking->id,
            'payment_gateway' => 'wallet',
        ]);
    }

    if ($isRemaining) {
        $booking->status = 'completed';
        $booking->update();

        $advance_paid = $booking->advance_paid_amount ?? 0;
        $total_amount = $booking->total_amount;
        $remaining_amount = $total_amount - $advance_paid;

        if ($remaining_amount > 0) {
            $admin_commission_amount = ($remaining_amount * $admin_commission_percentage) / 100;
            $provider_earning = $remaining_amount - $admin_commission_amount;

            Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_earning);
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

            ProviderPayout::create([
                'provider_id' => $booking->provider_id,
                'amount' => $provider_earning,
                'payment_method' => 'wallet',
                'paid_date' => Carbon::now(),
                'status' => 'paid',
                'booking_id' => $booking->id,
                'payment_gateway' => 'wallet',
            ]);
        }

        // Mark all commissions as paid
        CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
    }

    // Save payment history for handyman if provider is first handyman
    $firstHandymanId = optional($booking->handymanAdded->first())->handyman_id;
    $assignedUserData = User::find($firstHandymanId);

    if ($firstHandymanId && $assignedUserData->user_type == 'provider') {
        $payment_history = [
            'payment_id' => $result->id,
            'booking_id' => $result->booking_id,
            'parent_id' => $result->booking_id,
            'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
            'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
            'sender_id' => $request->customer_id,
            'receiver_id' => $firstHandymanId,
            'datetime' => $request->datetime,
            'total_amount' => $request->total_amount,
            'txn_id' => $request->txn_id,
            'type' => $request->payment_type,
            'text' => __('messages.payment_transfer', [
                'from' => get_user_name($request->customer_id),
                'to' => get_user_name($firstHandymanId),
                'amount' => getPriceFormat((float)$request->total_amount),
            ]),
        ];
        $res = PaymentHistory::create($payment_history);
        $res->parent_id = $res->id;
        $res->update();
    }

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

public function index(Request $request)
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
        $data['datetime'] = isset($request->datetime) ? date('Y-m-d H:i:s',strtotime($request->datetime)) : date('Y-m-d H:i:s');
        $result = Payment::create($data);
        $booking = Booking::find($request->booking_id);
        if(!empty($result) && $result->payment_status == 'advanced_paid'){
            $booking->advance_paid_amount  = $request->total_amount;
            $booking->status  = 'pending';

            $booking->update();

            // ✅ Manually split advance payment commission here
            $advance_paid_amount = $request->total_amount;

            // Example: get commission percentage from settings
            $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;

            // Calculate
            $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
            $provider_earning = $advance_paid_amount - $admin_commission_amount;

            // Add provider earning to wallet
            $provider_wallet = Wallet::where('user_id', $booking->provider_id)->first();
            if ($provider_wallet) {
                $provider_wallet->amount += $provider_earning;
                $provider_wallet->update();
            }

            // Add admin commission to admin wallet
            $admin_user_id = User::where('user_type', 'admin')->value('id');
            $admin_wallet = Wallet::where('user_id', $admin_user_id)->first();
            if ($admin_wallet) {
                $admin_wallet->amount += $admin_commission_amount;
                $admin_wallet->update();
            }

            // Optionally record it inside CommissionEarning table (separate record if you want)
            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid', // or 'paid' if you want
            ]);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'provider',
                'employee_id' => $booking->provider_id,
                'commission_amount' => $booking->total_amount - $provider_earning,
                'commission_status' => 'unpaid',
            ]);

            ProviderPayout::create([
                'provider_id' => $booking->provider_id,
                'amount' => $provider_earning, // Only provider's share of advance payment
                'payment_method' => 'wallet', // Payment done into wallet
                'paid_date' => Carbon::now(), // Current timestamp
                'status' => 'pending', // Payout not sent yet (only earned)
                'booking_id' => $booking->id, // Optional, if your table has booking_id field
                'payment_gateway' => 'wallet', // Optional, if your table has this
            ]);

        }

        $firstHandymanId = optional($booking->handymanAdded->first())->handyman_id;
        $assignedUserData = User::find($firstHandymanId);
        if($firstHandymanId != null && $assignedUserData->user_type == 'provider'){
            $payment_history = [
                'payment_id' => $result->id,
                'booking_id' => $result->booking_id,
                'parent_id' => $result->booking_id,
                'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
                'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
                'sender_id' => $request->customer_id,
                'receiver_id' => $firstHandymanId,
                'datetime' => $request->datetime,
                'total_amount' => $request->total_amount,
                'txn_id' => $request->txn_id,
                'type' => $request->payment_type,
                'text'     =>    __('messages.payment_transfer',['from' => get_user_name( $request->customer_id),'to' => get_user_name($firstHandymanId),'amount' => getPriceFormat((float)$request->total_amount) ]),
            ];
            $res =  PaymentHistory::create($payment_history);
            $res->parent_id = $res->id;
            $res->update();
        }
        $service_id = Booking::where('id',$request->booking_id)->pluck('service_id');
        $service = Service::where('id',$service_id)->first();
        $booking->payment_id = $result->id;
        $booking->update();

        if($booking->status == 'completed' && $result->payment_status=='paid'){
            CommissionEarning::where('booking_id',$booking->id)->update(['commission_status'=>'unpaid']);
        }
        $status_code = 200;

        $message = __('messages.payment_completed');
        $activity_data = [
            'activity_type' => 'payment_message_status',
            'payment_status'=>  $data['payment_status'],
            'booking_id' => $booking->id,
            'booking' => $booking,
            'booking_amount' => $request->total_amount,
        ];
        $this->sendNotification($activity_data);

        if($result->payment_status == 'failed')
        {
            $status_code = 400;
        }

        return comman_message_response($message,$status_code);
    }

    public function paymentList(Request $request)
    {
        $payment = Payment::myPayment()->with('booking');
        if($request->has('booking_id') && !empty($request->booking_id)){
            $payment->where('booking_id',$request->booking_id);
        }
        if($request->has('payment_type') && !empty($request->payment_type)){

            if($request->payment_type == 'cash'){
                $payment->where('payment_type',$request->payment_type);
            }
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id','desc')->paginate($per_page);
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

    public function transferPayment(Request $request){
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $admin = json_decode($sitesetup->value);
        $data = $request->all();
        $auth_user = authSession();
        $user_id = $auth_user->id;


        date_default_timezone_set( $admin->time_zone ?? 'UTC');
        $data['datetime'] = date('Y-m-d H:i:s');

        if($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.HANDYMAN_SEND_PROVIDER')){
            $data['text'] = __('messages.payment_transfer',
            ['from' => get_user_name($data['sender_id']),'to' => get_user_name($data['receiver_id']),'amount' => getPriceFormat((float)$data['total_amount']) ]);
        }
        if($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.PROVIDER_APPROVED_CASH')){
            $data['text'] = __('messages.cash_approved',['amount' => getPriceFormat((float)$data['total_amount']),'name' => get_user_name($data['receiver_id']) ]);
        }
        if($data['action'] == config('constant.PAYMENT_HISTORY_ACTION.PROVIDER_SEND_ADMIN')){
            $data['text'] =  __('messages.payment_transfer',['from' => get_user_name($data['sender_id']),'to' => get_user_name(admin_id()),
            'amount' => getPriceFormat((float)$data['total_amount']) ]);
        }
        $result = \App\Models\PaymentHistory::create($data);

        if($data['action'] == 'provider_approved_cash' && $data['status'] == 'approved_by_provider' ){

            $bookingdata = Booking::find($request->booking_id);
            $paymentdata = Payment::where('booking_id',$bookingdata->id)->first();
            if($bookingdata->payment_id != null){
                $payment_status = 'pending_by_admin';
                $paymentdata->update(['payment_status' => $payment_status]);
            }
        }
        $message = trans('messages.transfer');
        if($request->is('api/*')) {
            return comman_message_response($message);
		}
    }

    public function paymentHistory(Request $request){
        $booking_id = $request->booking_id;
        $payment = PaymentHistory::where('booking_id',$booking_id);

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id','desc')->paginate($per_page);
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

    public function getCashPaymentHistory(Request $request){
        $payment_id = $request->payment_id;
        $payment = PaymentHistory::where('payment_id',$payment_id)->with('booking');

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id','desc')->paginate($per_page);
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


    public function paymentDetail(Request $request){
        $auth_user = authSession();
        $user_id = $auth_user->id;

        $get_all_payments = PaymentHistory::query();
        if(!empty($request->status)){
            $get_all_payments = $get_all_payments->where('status',$request->status);
        }

        $user = auth()->user();
        $role = $user->hasAnyRole(['handyman', 'provider']) ? $user->getRoleNames()->first() : null;
        $status = $request->status ?? null;

        $roleActionMap = [
            'handyman' => [
                'approved_by_handyman' => ['action' => 'handyman_approved_cash', 'column' => 'receiver_id'],
                'pending_by_provider'  => ['action' => 'handyman_send_provider', 'column' => 'sender_id'],
                'approved_by_provider' => ['action' => 'provider_approved_cash', 'column' => 'sender_id'],
                'default'              => ['actions' => ['handyman_approved_cash', 'handyman_send_provider', 'provider_approved_cash', 'admin_approved_cash','provider_send_admin']],
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
                    ->where(function($query) use ($user_id) {
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
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $get_all_payments->count();
            }
        }


        // apply date filter
        if(!empty($request->from) && !empty($request->to)){
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
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id','desc')->paginate($per_page);
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

    public function paymentGateways(Request $request){
        $payment = PaymentGateway::where('status',1)->where('type', '!=', 'razorPayX')->get();
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
}
