<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\User;
use App\Traits\NotificationTrait;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class WalletPayPalController extends Controller
{
    use NotificationTrait;

    private $client;

    public function __construct()
    {
        $paymentGatewayValue = getPaymentMethodkey('paypal');
        $clientId     = $paymentGatewayValue['paypal_client_id'] ?? null;
        $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
        $mode         = $paymentGatewayValue['mode'] ?? 'sandbox';

        if (!$clientId || !$clientSecret) {
            $environment  = new SandboxEnvironment('invalid', 'invalid');
            $this->client = new PayPalHttpClient($environment);
            return;
        }

        $environment  = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Create a PayPal order for wallet top-up.
     * POST /api/wallet-paypal/create
     * Body: { amount: float }
     */
    public function createPayment(Request $request)
    {
        $amount = number_format((float) $request->input('amount', 0), 2, '.', '');

        if ((float) $amount <= 0) {
            return comman_custom_response(['status' => false, 'error' => 'Invalid or missing amount.'], 400);
        }

        try {
            $sitesetup     = \App\Models\Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
            $countryId     = $sitesetupdata['default_currency'] ?? null;
            $country       = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode  = strtoupper((string) ($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }

        $baseURL    = config('app.url') ?: 'https://frobster.com';
        $returnUrl  = $baseURL . '/api/wallet-paypal/success?amount=' . $amount;
        $cancelUrl  = $baseURL . '/api/wallet-paypal/cancel';

        $order = new OrdersCreateRequest();
        $order->prefer('return=representation');
        $order->body = [
            'intent'              => 'CAPTURE',
            'purchase_units'      => [[
                'amount'      => ['currency_code' => $currencyCode, 'value' => $amount],
                'description' => 'Wallet Top-Up',
            ]],
            'application_context' => [
                'cancel_url'   => $cancelUrl,
                'return_url'   => $returnUrl,
                'brand_name'   => env('APP_NAME'),
                'landing_page' => 'LOGIN',
                'user_action'  => 'PAY_NOW',
            ],
        ];

        try {
            $response    = $this->client->execute($order);
            $approvalUrl = collect($response->result->links)->firstWhere('rel', 'approve')->href;
            return comman_custom_response(['status' => true, 'url' => $approvalUrl]);
        } catch (\Exception $e) {
            return comman_custom_response(['status' => false, 'error' => 'PayPal error: ' . $e->getMessage()]);
        }
    }

    /**
     * PayPal redirects here after approval. Capture payment and credit wallet.
     * GET /api/wallet-paypal/success?token=...&user_id=...&amount=...
     */
    public function successApi(Request $request)
    {
        $token  = $request->query('token');
        $userId = auth()->id();
        $amount = (float) $request->query('amount', 0);

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Missing PayPal token.'], 400);
        }

        if (!$userId) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Capture the PayPal order
        try {
            $capture = new OrdersCaptureRequest($token);
            $capture->prefer('return=representation');
            $response = $this->client->execute($capture);

            if ($response->result->status !== 'COMPLETED') {
                return response()->json(['status' => false, 'message' => 'PayPal capture not completed.'], 400);
            }

            $txnId = $response->result->id;
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'PayPal capture error: ' . $e->getMessage()], 500);
        }

        // Credit the wallet
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found.'], 404);
            }
            $wallet = Wallet::create([
                'title'   => $user->display_name,
                'user_id' => $userId,
                'amount'  => 0,
            ]);
        }

        $wallet->amount += $amount;
        $wallet->save();

        $activity_data = [
            'activity_type'    => 'wallet_top_up',
            'wallet'           => $wallet,
            'top_up_amount'    => $amount,
            'transaction_type' => 'paypal',
            'transaction_id'   => $txnId,
        ];
        $this->sendNotification($activity_data);

        return response()->json([
            'status'         => true,
            'message'        => trans('messages.wallet_top_up', ['amount' => getPriceFormat($wallet->amount)]),
            'transaction_id' => $txnId,
            'amount'         => $amount,
        ]);
    }

    /**
     * PayPal cancel redirect.
     * GET /api/wallet-paypal/cancel
     */
    public function cancelApi(Request $request)
    {
        return response()->json(['status' => false, 'message' => 'Payment cancelled.']);
    }
}
