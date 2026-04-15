<?php

namespace App\Http\Controllers;

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

class PayPalController extends Controller
{
    use NotificationTrait;

    private $client;

    public function __construct()
    {
        // Read credentials strictly from settings
        $paymentGatewayValue = getPaymentMethodkey('paypal');
        $clientId = $paymentGatewayValue['paypal_client_id'] ?? null;
        $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
        $mode = $paymentGatewayValue['mode'] ?? 'sandbox';

        // Fail fast if not configured
        if (!$clientId || !$clientSecret) {
            // Create a dummy client to avoid null refs; calls will fail gracefully later
            $environment = new SandboxEnvironment('invalid', 'invalid');
            $this->client = new PayPalHttpClient($environment);
            return;
        }

        $environment = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        $this->client = new PayPalHttpClient($environment);
    }

    public function createPayment(Request $request)
    {
        $baseURL = config('app.url') ?: 'https://frobster.com';

        if ($request->type == 'full_payment') {
            $booking = Booking::find($request->booking_id);
            $amount = $booking->total_amount - $booking->advance_paid_amount;
        } else {
            $amount = number_format((float)$request->total_amount, 2, '.', '');
        }

        $order = new OrdersCreateRequest();

        // Resolve currency dynamically from settings (site-setup -> default_currency -> Country)
        try {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string)($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }
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
                'cancel_url' => route('paypal.cancel'),
                'return_url' => $baseURL . '/paypal-success/' . $request->booking_id . '?type=' . $request->type,
                'brand_name' => env('APP_NAME'),
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
            ]
        ];

        try {
            $response = $this->client->execute($order);

            $approvalLink = collect($response->result->links)->firstWhere('rel', 'approve')->href;

            return comman_custom_response([
                'url' => $approvalLink
            ]);
        } catch (\Exception $e) {
            return comman_custom_response([
                'error' => 'PayPal Create Payment Error: ' . $e->getMessage()
            ]);
        }
    }

    public function createSubscriptionPayment(Request $request)
    {
        $baseURL = config('app.url') ?: 'https://frobster.com';
        
        $amount = number_format((float)$request->amount, 2, '.', '');

        $order = new OrdersCreateRequest();
        $order->prefer('return=representation');

        // Resolve currency dynamically from settings (site-setup -> default_currency -> Country)
        try {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string)($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }
        $order->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $amount
                ],
                'description' => 'Subscription Plan: ' . $request->plan_type
            ]],
            'application_context' => [
                'cancel_url' => $baseURL . '/provider_info/' . auth()->id(),
                'return_url' => $baseURL . '/subscription/paypal/success/' . auth()->id() . '?plan_type=' . urlencode($request->plan_type),
                'brand_name' => env('APP_NAME'),
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
            ]
        ];

        try {
            $response = $this->client->execute($order);
            $approvalLink = collect($response->result->links)->firstWhere('rel', 'approve')->href;

            return comman_custom_response([
                'url' => $approvalLink
            ]);
        } catch (\Exception $e) {
            return comman_custom_response([
                'error' => 'PayPal Subscription Payment Error: ' . $e->getMessage()
            ]);
        }
    }

    public function subscriptionSuccess(Request $request, $id)
    {
        $token = $request->query('token');
        $plan_type = $request->query('plan_type');
        $user_id = $id;

        if (!$token || !$plan_type) {
            return redirect()->route('provider_info', $user_id)->with('error', 'Invalid PayPal payment session.');
        }

        try {
            $captureRequest = new OrdersCaptureRequest($token);
            $captureRequest->prefer('return=representation');
            $response = $this->client->execute($captureRequest);

            if ($response->result->status === 'COMPLETED') {
                // Get the new plan details
                $newPlan = \App\Models\Plans::where('title', $plan_type)->first();
                if (!$newPlan) {
                    return redirect()->route('provider_info', $user_id)->with('error', 'Plan not found.');
                }

                // Get current user's active subscription
                $get_existing_plan = get_user_active_plan($user_id);
                $active_plan_left_days = 0;
                
                // Calculate remaining days from current plan
                if ($get_existing_plan) {
                    $active_plan_left_days = check_days_left_plan($get_existing_plan, ['start_at' => now()]);
                    
                    // Deactivate existing plan if switching to different plan
                    if ($plan_type != $get_existing_plan->plan_type) {
                        $existing_subscription = \App\Models\ProviderSubscription::where('user_id', $user_id)
                            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
                            ->first();
                        if ($existing_subscription) {
                            $existing_subscription->update([
                                'status' => config('constant.SUBSCRIPTION_STATUS.INACTIVE')
                            ]);
                        }
                    }
                }

                // Create new subscription
                $data = [
                    'plan_id' => $newPlan->id,
                    'user_id' => $user_id,
                    'title' => $newPlan->title,
                    'identifier' => $newPlan->identifier,
                    'type' => $newPlan->type,
                    'amount' => $newPlan->amount,
                    'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'),
                    'start_at' => now(),
                    'end_at' => get_plan_expiration_date(now(), $newPlan->type, $active_plan_left_days, $newPlan->duration),
                    'duration' => $newPlan->duration,
                    'description' => $newPlan->description,
                    'plan_type' => $newPlan->plan_type,
                    'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
                ];

                $result = \App\Models\ProviderSubscription::create($data);

                if ($result) {
                    // Create payment transaction
                    $payment_data = [
                        'subscription_plan_id' => $result->id,
                        'user_id' => $result->user_id,
                        'amount' => $result->amount,
                        'payment_status' => 'paid',
                        'payment_type' => 'paypal',
                        'txn_id' => $response->result->id,
                    ];
                    $payment = \App\Models\SubscriptionTransaction::create($payment_data);

                    // Update subscription to active
                    $result->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                    $result->payment_id = $payment->id;
                    $result->save();

                    // Update user subscription status
                    $user = \App\Models\User::find($user_id);
                    $user->is_subscribe = 1;
                    $user->save();

                    // Send subscription upgrade email
                    sendSubscriptionUpgradeEmail($user, $result, 'paypal', $response->result->id);

                    return redirect()->route('provider_info', $user_id)->with('success', 'Subscription upgraded successfully!');
                }

                return redirect()->route('provider_info', $user_id)->with('error', 'Failed to create subscription.');
            } else {
                return redirect()->route('provider_info', $user_id)->with('error', 'Payment was not completed.');
            }
        } catch (\Exception $e) {
            \Log::error('PayPal subscription payment verification failed: ' . $e->getMessage());
            return redirect()->route('provider_info', $user_id)->with('error', 'Payment verification failed.');
        }
    }

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
                $result = Payment::where('booking_id', $id)->latest()->first();
                $booking = Booking::find($id);

                if ($type == 'advance_payment') {
                    $result->payment_status = 'advanced_paid';
                } else {
                    $result->payment_status = 'paid';
                }

                // ✅ Identify receiver ( first handyman assigned to booking )
                $firstHandymanId = optional($booking->handymanAdded()->first())->id;
                $assignedUserData = optional(User::find($firstHandymanId));

                if ($firstHandymanId && $assignedUserData->user_type == 'provider') {
                    $payment_history = [
                        'payment_id' => $result->id,
                        'booking_id' => $result->booking_id,
                        'parent_id' => $result->booking_id, // temporary, will update below
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
                    //                $booking->status = 'pending';

                    $advance_paid_amount = $result->total_amount;
                    $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;

                    $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;

                    // Hold provider advance payout; credit admin only
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
                    
                    // Admin: 10% on remaining amount only (not on extra charges); once per remaining payment
                    $remaining_admin_commission = ($remaining_amount > 0)
                        ? ($remaining_amount * $admin_commission_percentage) / 100
                        : 0;

                    $provider_side_advance = ($advance_paid * (100 - $admin_commission_percentage)) / 100;
                    // Provider + handymen get 90% of remaining amount
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
                        $commission_percent = max(1, min(99, $handyman->handyman_commission));
                        $handyman_share = ($pool * $commission_percent) / 100;
                        $total_handyman_share += $handyman_share;
                        $handyman_payouts[] = [
                            'handyman_id' => $handyman_id,
                            'amount' => $handyman_share,
                        ];
                    }

                    $provider_from_pool = $pool - $total_handyman_share;
                    if ($provider_from_pool < 0) {
                        $provider_from_pool = 0;
                    }
                    // Extra charges 100% to provider (no admin commission on extra)
                    $provider_extra_earning = $extra_total;
                    $provider_final_earning = $provider_from_pool + $provider_extra_earning;

                    foreach ($handyman_payouts as $payout) {
                        Wallet::firstOrCreate(['user_id' => $payout['handyman_id']])->increment('amount', $payout['amount']);

                        HandymanPayout::create([
                            'handyman_id' => $payout['handyman_id'],
                            'booking_id' => $booking->id,
                            'payment_id' => $result->id ?? null,
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
                        'payment_id' => $result->id ?? null,
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
                    'payment_status' => str_replace("_", " ", ucfirst($result->payment_status)),
                    'booking_id' => $booking->id,
                    'booking' => $booking,
                ];
                $this->sendNotification($activity_data);

                return redirect('/booking-list');
            }

            return redirect()->back()->with('error', 'Payment not completed');
        } catch (\Exception $e) {
            \Log::error("PayPal Success Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }






    public function cancel()
    {
        return redirect()->back()->with('error', 'Payment was canceled');
    }
}
