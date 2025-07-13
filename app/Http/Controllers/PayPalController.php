<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CommissionEarning;
use App\Models\Payment;
use App\Models\ProviderPayout;
use App\Models\Setting;
use App\Models\User;
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
        $clientId = 'Afyzu7BkzUMiiK6kdB0QutzIh3cSZTcCFZjko7Fl1boR_jhW034YWoyVUBnsSmu1ZbX5bdIY0HA49SY6';
        $clientSecret = 'EET9cnIPbSWpA6c96cqJOSI9c-feQ512YQEZXSatXCcehOJU7G9eoxnPrNoikb-lFaCt0oOTAciZ26Ka';
        $mode = env( 'PAYPAL_MODE', 'sandbox' );

        $environment = $mode === 'live'
        ? new ProductionEnvironment( $clientId, $clientSecret )
        : new SandboxEnvironment( $clientId, $clientSecret );

        $this->client = new PayPalHttpClient( $environment );
    }

    public function createPayment( Request $request )
 {
        $baseURL = env( 'APP_URL' );

        if ( $request->type == 'full_payment' ) {
            $booking = Booking::find( $request->booking_id );
            $amount = $booking->total_amount - $booking->advance_paid_amount;
        } else {
            $amount = number_format( ( float )$request->total_amount, 2, '.', '' );
        }

        $order = new OrdersCreateRequest();
        $order->prefer( 'return=representation' );
        $order->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [ [
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $amount
                ],
                'description' => 'Payment for Booking #' . $request->booking_id
            ] ],
            'application_context' => [
                'cancel_url' => route( 'paypal.cancel' ),
                'return_url' => $baseURL . '/paypal-success/' . $request->booking_id . '?type=' . $request->type,
                'brand_name' => env( 'APP_NAME' ),
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
            ]
        ];

        try {
            $response = $this->client->execute( $order );

            $approvalLink = collect( $response->result->links )->firstWhere( 'rel', 'approve' )->href;

            return comman_custom_response( [
                'url' => $approvalLink
            ] );
        } catch ( \Exception $e ) {
            return comman_custom_response( [
                'error' => 'PayPal Create Payment Error: ' . $e->getMessage()
            ] );
        }
    }

    public function success( Request $request )
 {
        $token = $request->query( 'token' );
        $id = $request->booking_id;
        $type = $request->type;

        if ( !$token ) {
            return redirect()->back()->with( 'error', 'Missing PayPal token' );
        }

        $captureRequest = new OrdersCaptureRequest( $token );
        $captureRequest->prefer( 'return=representation' );

        try {
            $response = $this->client->execute( $captureRequest );

            if ( $response->statusCode === 201 || $response->statusCode === 200 ) {
                $result = Payment::where( 'booking_id', $id )->first();

                if ( $type == 'advance_payment' ) {
                    $result->payment_status = 'advanced_paid';
                } else {
                    $result->payment_status = 'paid';
                }

                $result->update();

                $booking = Booking::find( $id );
                if ( !empty( $result ) && $result->payment_status == 'advanced_paid' ) {
                    $booking->advance_paid_amount = $result->total_amount;
                    $booking->status = 'pending';

                    // ✅ Manually split advance payment commission here
                    $advance_paid_amount = $result->total_amount;

                    // Example: get commission percentage from settings
                    $admin_commission_percentage = Setting::getValueByKey( 'admin_commission_percentage', 'site-setup' )->value ?? 10;

                    // Calculate
                    $admin_commission_amount = ( $advance_paid_amount * $admin_commission_percentage ) / 100;
                    $provider_earning = $advance_paid_amount - $admin_commission_amount;

                    // Add provider earning to wallet
                    $provider_wallet = Wallet::where( 'user_id', $booking->provider_id )->first();
                    if ( $provider_wallet ) {
                        $provider_wallet->amount += $provider_earning;
                        $provider_wallet->update();
                    }

                    // Add admin commission to admin wallet
                    $admin_user_id = User::where( 'user_type', 'admin' )->value( 'id' );
                    $admin_wallet = Wallet::where( 'user_id', $admin_user_id )->first();
                    if ( $admin_wallet ) {
                        $admin_wallet->amount += $admin_commission_amount;
                        $admin_wallet->update();
                    }

                    // Optionally record it inside CommissionEarning table ( separate record if you want )
                    CommissionEarning::create( [
                        'booking_id' => $booking->id,
                        'user_type' => 'admin',
                        'employee_id' => $admin_user_id,
                        'commission_amount' => $admin_commission_amount,
                        'commission_status' => 'paid', // or 'paid' if you want
                    ] );

                    CommissionEarning::create( [
                        'booking_id' => $booking->id,
                        'user_type' => 'provider',
                        'employee_id' => $booking->provider_id,
                        'commission_amount' => $booking->total_amount - $provider_earning,
                        'commission_status' => 'unpaid',
                    ] );

                    ProviderPayout::create( [
                        'provider_id' => $booking->provider_id,
                        'amount' => $provider_earning, // Only provider's share of advance payment
                        'payment_method' => 'paypal', // Payment done into wallet
                        'paid_date' => Carbon::now(), // Current timestamp
                        'status' => 'paid', // Payout not sent yet (only earned)
                        'booking_id' => $booking->id, // Optional, if your table has booking_id field
                        'payment_gateway' => 'paypal', // Optional, if your table has this
                    ]);
                }

                if(!empty($result) && $result->payment_status == 'paid'){
                    $booking->status = 'completed';
                    $booking->update();

                    $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
                    $admin_user_id = User::where('user_type', 'admin')->value('id');

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
                            'payment_method' => 'paypal',
                            'paid_date' => Carbon::now(),
                            'status' => 'paid',
                            'booking_id' => $booking->id,
                            'payment_gateway' => 'paypal',
                        ]);
                         CommissionEarning::create([
                            'booking_id' => $booking->id,
                            'user_type' => 'admin',
                            'employee_id' => $admin_user_id,
                            'commission_amount' => $admin_commission_amount,
                            'commission_status' => 'paid', // Already paid in remaining
                        ]);

                    CommissionEarning::create([
                        'booking_id' => $booking->id,
                        'user_type' => 'provider',
                        'employee_id' => $booking->provider_id,
                        'commission_amount' => $provider_earning,
                        'commission_status' => 'paid',
                    ]);
                            }

                    // Mark all commissions as paid
                    CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
                }
                $booking->payment_id = $result->id;
                $booking->update();

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
        return redirect()->back()->with('error', 'Payment was canceled' );
                    }
                }
