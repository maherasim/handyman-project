<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CommissionEarning;
use App\Models\Payment;
use App\Models\ProviderPayout;
use App\Models\Setting;
use App\Models\User;
use App\Models\BookingHandymanMapping;
use App\Models\HandymanPayout;
use App\Models\PaymentHistory;
use App\Models\Wallet;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdvancePaymentNotificationMail;
use App\Mail\FullPaymentReceivedMail;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

/**
 * Standalone API for booking PayPal flow (create → success/cancel).
 * Does not modify existing PayPalController or save-payment.
 */
class BookingPayPalController extends Controller
{
    use NotificationTrait;

    private $client;

    public function __construct()
    {
        $paymentGatewayValue = getPaymentMethodkey('paypal');
        $clientId = $paymentGatewayValue['paypal_client_id'] ?? null;
        $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
        $mode = $paymentGatewayValue['mode'] ?? 'sandbox';

        if (!$clientId || !$clientSecret) {
            $environment = new SandboxEnvironment('invalid', 'invalid');
            $this->client = new PayPalHttpClient($environment);
            return;
        }

        $environment = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Create PayPal order for booking. Id from URL (create/{id}) or from body (booking_id).
     */
    public function createPayment(Request $request, $id = null)
    {
        $baseURL = config('app.url') ?: 'https://frobster.com';
        $bookingId = (int) ($id ?? $request->input('booking_id'));
        if ($bookingId <= 0) {
            return comman_custom_response(['status' => false, 'error' => 'Missing or invalid booking id.'], 400);
        }

        // full_payment: amount from booking; else: type + amount from body (like postjob)
        $type = strtolower((string) $request->input('type', 'advance'));
        if ($type === 'full_payment') {
            $booking = Booking::find($bookingId);
            $amount = $booking ? ($booking->total_amount - ($booking->advance_paid_amount ?? 0)) : 0;
        } else {
            $amount = $request->input('amount') ?? $request->input('total_amount');
            $amount = number_format((float)$amount, 2, '.', '');
        }
        if ((float)$amount <= 0) {
            return comman_custom_response(['status' => false, 'error' => 'Invalid or missing payment amount.'], 400);
        }

        // Create pending Payment so success callback can find and update it (same as web flow expects)
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return comman_custom_response(['status' => false, 'error' => 'Booking not found.'], 404);
        }
        $existing = Payment::where('booking_id', $bookingId)->where('payment_type', 'paypal')->where('payment_status', 'pending')->first();
        if ($existing) {
            $existing->update(['total_amount' => (float) $amount, 'datetime' => now()->format('Y-m-d H:i:s')]);
        } else {
            Payment::create([
                'booking_id' => $bookingId,
                'customer_id' => $booking->customer_id,
                'datetime' => now()->format('Y-m-d H:i:s'),
                'total_amount' => (float) $amount,
                'payment_type' => 'paypal',
                'payment_status' => 'pending',
                'txn_id' => null,
            ]);
        }

        $order = new OrdersCreateRequest();

        try {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string)($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }

        $returnUrl = $baseURL . '/api/booking-paypal/success/' . $bookingId . '?type=' . $type;
        $cancelUrl = $baseURL . '/api/booking-paypal/cancel';

        $order->prefer('return=representation');
        $order->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $amount
                ],
                'description' => 'Payment for Booking #' . $bookingId
            ]],
            'application_context' => [
                'cancel_url' => $cancelUrl,
                'return_url' => $returnUrl,
                'brand_name' => env('APP_NAME'),
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
            ]
        ];

        try {
            $response = $this->client->execute($order);
            $approvalLink = collect($response->result->links)->firstWhere('rel', 'approve')->href;
            return comman_custom_response(['status' => true, 'url' => $approvalLink]);
        } catch (\Exception $e) {
            return comman_custom_response(['status' => false, 'error' => 'PayPal Create Payment Error: ' . $e->getMessage()]);
        }
    }

    /**
     * PayPal redirects here after payment. Capture order and run full booking payment flow; return JSON.
     * When type is missing (e.g. app calls without type), default to advance_payment so booking is not marked completed.
     * Idempotent: if this payment was already processed (has txn_id), return success without re-running.
     */
    public function successApi(Request $request, $booking_id)
    {
        $token = $request->query('token');
        $type = (string) $request->query('type', '');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Missing PayPal token'], 400);
        }

        $payment = Payment::where('booking_id', (int) $booking_id)->latest()->first();
        if ($payment && $payment->txn_id) {
            return response()->json([
                'status' => true,
                'message' => __('messages.payment_success_proceed'),
                'booking_id' => (int) $booking_id,
            ]);
        }

        if ($type === '') {
            $type = 'advance_payment';
        }

        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $this->client->execute($captureRequest);
            if ($response->statusCode !== 201 && $response->statusCode !== 200) {
                return response()->json(['status' => false, 'message' => 'Payment not completed'], 400);
            }

            $paypalTxnId = $response->result->id ?? null;
            $status = $this->handleBookingSuccess((int) $booking_id, $type, $paypalTxnId);

            if ($status) {
                return response()->json([
                    'status' => true,
                    'message' => __('messages.payment_success_proceed'),
                    'booking_id' => (int) $booking_id,
                ]);
            }
            return response()->json(['status' => false, 'message' => 'Booking or Payment record not found'], 404);
        } catch (\Exception $e) {
            \Log::error('BookingPayPal API Success Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Web redirect flow (if used from dashboard): capture then redirect to booking-list.
     */
    public function success(Request $request, $booking_id = null)
    {
        $token = $request->query('token');
        $id = $booking_id ?? $request->booking_id;
        $type = $request->type;

        if (!$token) {
            return redirect()->back()->with('error', 'Missing PayPal token');
        }

        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $this->client->execute($captureRequest);

            if ($response->statusCode === 201 || $response->statusCode === 200) {
                $paypalTxnId = $response->result->id ?? null;
                $this->handleBookingSuccess((int) $id, (string) $type, $paypalTxnId);
                return redirect('/booking-list');
            }

            return redirect()->back()->with('error', 'Payment not completed');
        } catch (\Exception $e) {
            \Log::error("PayPal Success Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Unified logic for processing booking after PayPal capture.
     */
    private function handleBookingSuccess(int $id, string $type, ?string $paypalTxnId): bool
    {
        $result = Payment::where('booking_id', $id)->latest()->first();
        $booking = Booking::find($id);

        if (!$result || !$booking) {
            return false;
        }

        if ($paypalTxnId) {
            $result->txn_id = $paypalTxnId;
        }

        // App may send type "advance" or "advance_payment"
        $isAdvance = in_array((string)$type, ['advance', 'advance_payment'], true);
        $result->payment_status = $isAdvance ? 'advanced_paid' : 'paid';

        // Identify receiver ( first handyman assigned to booking )
        $firstHandymanId = optional($booking->handymanAdded()->first())->id;
        $assignedUserData = optional(User::find($firstHandymanId));

        if ($firstHandymanId && $assignedUserData->user_type == 'provider') {
            $payment_history = [
                'payment_id' => $result->id,
                'booking_id' => $result->booking_id,
                'parent_id' => $result->booking_id, 
                'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
                'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
                'sender_id' => $booking->customer_id,
                'receiver_id' => $firstHandymanId,
                'datetime' => now(),
                'total_amount' => (float)$result->total_amount,
                'txn_id' => $result->txn_id,
                'type' => $result->payment_type,
                'text' => __('messages.payment_transfer', [
                    'from' => get_user_name($booking->customer_id),
                    'to' => get_user_name($firstHandymanId),
                    'amount' => getPriceFormat((float)$result->total_amount),
                ]),
            ];

            $res = PaymentHistory::create($payment_history);
            $res->parent_id = $res->id;
            $res->save();
        }

        if ($result->payment_status == 'advanced_paid') {
            $booking->advance_paid_amount = $result->total_amount;
            $advance_paid_amount = $result->total_amount;
            $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
            $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;

            $admin_user_id = User::where('user_type', 'admin')->value('id');
            if ($admin_user_id) {
                Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'admin',
                    'employee_id' => $admin_user_id,
                    'commission_amount' => $admin_commission_amount,
                    'commission_status' => 'paid',
                ]);
            }

            // Send email notification to provider about advance payment
            try {
                $booking->load(['customer', 'service']);
                $provider = User::find($booking->provider_id);
                if ($provider && $provider->email) {
                    Mail::to($provider->email)->locale(getRecipientLocale($provider))->send(new AdvancePaymentNotificationMail($provider, $result, $booking, getRecipientLocale($provider)));
                    \Log::info('Advance payment notification email sent to provider: ' . $provider->email . ' for booking ID: ' . $booking->id);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send advance payment notification email: ' . $e->getMessage());
            }
        }

        if ($result->payment_status == 'paid') {
            $booking->status = 'completed';
            $booking->update();

            $advance_paid = $booking->advance_paid_amount ?? 0;
            $total_amount = $booking->total_amount;
            $remaining_amount = $total_amount - $advance_paid;

            $result->total_amount = $remaining_amount;
            $result->save();

            // Idempotency: skip payout creation if provider commission already credited
            $providerAlreadyPaid = CommissionEarning::where('booking_id', $booking->id)
                ->where('user_type', 'provider')
                ->where('commission_status', 'paid')
                ->whereNull('post_job_bid_request_id')
                ->exists();

            if (!$providerAlreadyPaid) {
                $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
                $admin_user_id = User::where('user_type', 'admin')->value('id');

                $extra_total = $booking->getExtraChargeValue();

                $remaining_admin_commission = ($remaining_amount > 0)
                    ? ($remaining_amount * $admin_commission_percentage) / 100
                    : 0;

                $provider_side_advance = ($advance_paid * (100 - $admin_commission_percentage)) / 100;
                $provider_side_remaining = ($remaining_amount * 90) / 100;
                $pool = $provider_side_advance + max(0, $provider_side_remaining - $extra_total);

                $handymen = BookingHandymanMapping::where('booking_id', $booking->id)->pluck('handyman_id');
                $handyman_payouts = [];
                $total_handyman_share = 0;
                foreach ($handymen as $handyman_id) {
                    $handyman = User::find($handyman_id);
                    if (!$handyman) continue;
                    $bookingCommission = $booking->handyman_commission;
                    if ($bookingCommission !== null && $bookingCommission > 0) {
                        $commission_percent = max(1, min(99, $bookingCommission));
                    } elseif ($handyman->handyman_commission !== null) {
                        $commission_percent = max(1, min(99, $handyman->handyman_commission));
                    } else {
                        continue;
                    }
                    $handyman_share = ($pool * $commission_percent) / 100;
                    $total_handyman_share += $handyman_share;
                    $handyman_payouts[] = ['handyman_id' => $handyman_id, 'amount' => $handyman_share];
                }

                $provider_from_pool = max(0, $pool - $total_handyman_share);
                $provider_extra_earning = $extra_total;
                $provider_final_earning = $provider_from_pool + $provider_extra_earning;

                foreach ($handyman_payouts as $payout) {
                    Wallet::firstOrCreate(['user_id' => $payout['handyman_id']])->increment('amount', $payout['amount']);
                    HandymanPayout::create([
                        'handyman_id' => $payout['handyman_id'],
                        'booking_id' => $booking->id,
                        'amount' => $payout['amount'],
                        'status' => 'paid',
                        'paid_date' => Carbon::now(),
                        'payment_method' => 'paypal',
                        'payment_gateway' => 'paypal',
                    ]);

                    CommissionEarning::create([
                        'booking_id' => $booking->id,
                        'user_type' => 'handyman',
                        'employee_id' => $payout['handyman_id'],
                        'commission_amount' => $payout['amount'],
                        'commission_status' => 'paid',
                    ]);
                }

                if ($remaining_admin_commission > 0 && $admin_user_id) {
                    Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $remaining_admin_commission);

                    CommissionEarning::create([
                        'booking_id' => $booking->id,
                        'user_type' => 'admin',
                        'employee_id' => $admin_user_id,
                        'commission_amount' => $remaining_admin_commission,
                        'commission_status' => 'paid',
                    ]);
                }

                Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_final_earning);

                ProviderPayout::create([
                    'provider_id' => $booking->provider_id,
                    'amount' => $provider_final_earning,
                    'payment_method' => 'paypal',
                    'paid_date' => Carbon::now(),
                    'status' => 'paid',
                    'booking_id' => $booking->id,
                    'payment_gateway' => 'paypal',
                ]);

                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'provider',
                    'employee_id' => $booking->provider_id,
                    'commission_amount' => $provider_final_earning,
                    'commission_status' => 'paid',
                ]);

                CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
            } // end !$providerAlreadyPaid

            // Send email notification to provider about full payment received
            try {
                $booking->load(['customer', 'service']);
                $provider = User::find($booking->provider_id);
                if ($provider && $provider->email) {
                    Mail::to($provider->email)->locale(getRecipientLocale($provider))->send(new FullPaymentReceivedMail($provider, $result, $booking, getRecipientLocale($provider)));
                    \Log::info('Full payment received notification email sent to provider: ' . $provider->email . ' for booking ID: ' . $booking->id);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send full payment received notification email: ' . $e->getMessage());
            }
        }

        $booking->payment_id = $result->id;
        $booking->update();
        $result->update();

        $activity_data = [
            'activity_type' => 'payment_message_status',
            'payment_status' => str_replace("_", " ", ucfirst($result->payment_status)),
            'booking_id' => $booking->id,
            'booking' => $booking,
        ];
        $this->sendNotification($activity_data);

        return true;
    }
    public function cancelApi(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => __('messages.payment_cancelled_return_to_app'),
        ]);
    }

    public function cancel()
    {
        return redirect()->back()->with('error', 'Payment was canceled');
    }
}
