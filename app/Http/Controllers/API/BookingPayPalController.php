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
     * Create PayPal order for booking; return/cancel URLs point to this API (for app).
     */
    public function createPayment(Request $request)
    {
        $baseURL = config('app.url') ?: 'https://frobster.com';

        // Same logic as PayPalController::createPayment
        if ($request->type == 'full_payment') {
            $booking = Booking::find($request->booking_id);
            $amount = $booking->total_amount - $booking->advance_paid_amount;
        } else {
            $amount = number_format((float)$request->total_amount, 2, '.', '');
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

        $returnUrl = $baseURL . '/api/booking-paypal/success/' . $request->booking_id . '?type=' . $request->type;
        $cancelUrl = $baseURL . '/api/booking-paypal/cancel';

        $order->prefer('return=representation');
        $order->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $amount
                ],
                'description' => 'Payment for Booking #' . $request->booking_id
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
            return comman_custom_response(['url' => $approvalLink]);
        } catch (\Exception $e) {
            return comman_custom_response(['error' => 'PayPal Create Payment Error: ' . $e->getMessage()]);
        }
    }

    /**
     * PayPal redirects here after payment. Capture order and run full booking payment flow; return JSON.
     */
    public function successApi(Request $request, $booking_id)
    {
        $token = $request->query('token');
        $type = (string) $request->query('type', '');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Missing PayPal token'], 400);
        }

        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $this->client->execute($captureRequest);
            if ($response->statusCode !== 201 && $response->statusCode !== 200) {
                return response()->json(['status' => false, 'message' => 'Payment not completed'], 400);
            }
            $this->processBookingPayPalSuccess((int) $booking_id, $type);
            return response()->json([
                'status' => true,
                'message' => __('messages.payment_success_proceed'),
                'booking_id' => (int) $booking_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('BookingPayPal API Success Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PayPal redirects here on cancel. Return JSON for app.
     */
    public function cancelApi(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => __('messages.payment_cancelled_return_to_app'),
        ]);
    }

    /**
     * Process booking after PayPal capture: payment, commission, wallet, notifications.
     * Shared by successApi() (JSON) and success() (redirect).
     */
    protected function processBookingPayPalSuccess(int $id, string $type): void
    {
        $result = Payment::where('booking_id', $id)->latest()->first();
        $booking = Booking::find($id);
        if (!$result || !$booking) {
            return;
        }

        $result->payment_status = ($type == 'advance_payment') ? 'advanced_paid' : 'paid';

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
                'total_amount' => $result->total_amount,
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
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);
            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid',
            ]);
        }

        if ($result->payment_status == 'paid') {
            $booking->status = 'completed';
            $booking->update();
            $advance_paid = $booking->advance_paid_amount ?? 0;
            $total_amount = $booking->total_amount;
            $remaining_amount = $total_amount - $advance_paid;
            $result->total_amount = $remaining_amount;
            $result->save();
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
                if (!$handyman || $handyman->handyman_commission === null) {
                    continue;
                }
                $commission_percent = max(1, min(85, $handyman->handyman_commission));
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
            CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
        }

        $booking->payment_id = $result->id;
        $booking->update();
        $result->update();
        $activity_data = [
            'activity_type' => 'payment_message_status',
            'payment_status' => str_replace('_', ' ', ucfirst($result->payment_status)),
            'booking_id' => $booking->id,
            'booking' => $booking,
        ];
        $this->sendNotification($activity_data);
    }

    /**
     * Web redirect flow (if used from dashboard): capture then redirect to booking-list.
     */
    public function success(Request $request)
    {
        $token = $request->query('token');
        $id = $request->booking_id;
        $type = $request->type;

        if (!$token) {
            return redirect()->back()->with('error', 'Missing PayPal token');
        }

        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $this->client->execute($captureRequest);

            if ($response->statusCode === 201 || $response->statusCode === 200) {
                $this->processBookingPayPalSuccess((int) $id, (string) $type);
                return redirect('/booking-list');
            }

            return redirect()->back()->with('error', 'Payment not completed');
        } catch (\Exception $e) {
            \Log::error("PayPal Success Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}
