<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PostJobBid;
use App\Models\PaymentPostJOb;
use App\Models\PaymentPostJObHistory;
use App\Models\CommissionEarning;
use App\Models\ProviderPayout;
use App\Models\User;
use App\Models\Setting;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            if (!class_exists(\Stripe\Webhook::class)) {
                return response()->json(['error' => 'Stripe SDK missing'], 500);
            }
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $intent = $event->data->object;
                $this->handlePaymentIntentSucceeded($intent);
                break;

            // For legacy Checkout flow if used
            case 'checkout.session.completed':
                $session = $event->data->object;
                if (!empty($session->payment_intent)) {
                    // Retrieve intent to get full metadata
                    $payment_geteway_value = getPaymentMethodkey('stripe');
                    $stripe_secret = $payment_geteway_value['stripe_key'] ?? null;
                    if ($stripe_secret) {
                        $stripe = new \Stripe\StripeClient($stripe_secret);
                        try {
                            $intent = $stripe->paymentIntents->retrieve($session->payment_intent, []);
                            $this->handlePaymentIntentSucceeded($intent);
                        } catch (\Throwable $e) {
                            Log::error('Stripe webhook retrieve intent failed: ' . $e->getMessage());
                        }
                    }
                }
                break;
        }

        return response()->json(['received' => true]);
    }

    private function handlePaymentIntentSucceeded($intent): void
    {
        try {
            $metadata = (array) ($intent->metadata ?? []);
            $bidId = isset($metadata['bid_id']) ? (int) $metadata['bid_id'] : null;
            $type = isset($metadata['type']) ? strtolower((string) $metadata['type']) : 'advance'; // advance|remaining

            if (empty($bidId)) {
                Log::warning('Stripe PI succeeded without bid_id metadata');
                return;
            }

            $bid = PostJobBid::find($bidId);
            if (!$bid) {
                Log::warning('Stripe PI for unknown bid_id: ' . $bidId);
                return;
            }

            $txnId = $intent->id;
            // Idempotency: skip if we've already recorded this txn
            if (PaymentPostJOb::where('txn_id', $txnId)->exists()) {
                return;
            }

            $amountReceived = (float) (($intent->amount_received ?? $intent->amount ?? 0) / 100.0);

            // Commission setup
            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
                ? (float) $adminCommissionSetting->value
                : 10.0;

            $adminCommissionAmount = ($amountReceived * $adminCommissionPercent) / 100.0;
            $providerEarningAmount = $amountReceived - $adminCommissionAmount;

            $finalPayment = PaymentPostJOb::create([
                'customer_id' => $bid->customer_id,
                'provider_id' => $bid->provider_id,
                'post_job_bid_request_id' => $bid->id,
                'total_amount' => $amountReceived,
                'discount' => 0,
                'payment_type' => 'stripe',
                'payment_status' => 'completed',
                'status' => $type,
                'txn_id' => $txnId,
                'other_transaction_detail' => json_encode([
                    'payment_intent_id' => $txnId,
                    'admin_commission' => $adminCommissionAmount,
                    'provider_earning' => $providerEarningAmount,
                ]),
            ]);

            $adminUser  = User::where('user_type', 'admin')->first();
            $providerId = $bid->provider_id;

            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'admin',
                    'employee_id'         => $adminUser?->id ?? 1,
                    'commission_amount'   => $adminCommissionAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            if ($providerEarningAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'provider',
                    'employee_id'         => $providerId,
                    'commission_amount'   => $providerEarningAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            if ($providerEarningAmount > 0) {
                ProviderPayout::create([
                    'provider_id'         => $providerId,
                    'amount'              => $providerEarningAmount,
                    'payment_method'      => 'Stripe',
                    'paid_date'           => now(),
                    'status'              => 'paid',
                    'booking_id'          => null,
                    'post_job_request_id' => $bid->id,
                    'payment_gateway'     => 'Stripe',
                ]);
            }

            PaymentPostJObHistory::create([
                'payment_id'  => $finalPayment->id,
                'parent_id'   => null,
                'action'      => 'customer_send_provider',
                'status'      => 'completed',
                'sender_id'   => $finalPayment->customer_id,
                'receiver_id' => $providerId,
                'datetime'    => now(),
                'total_amount' => $finalPayment->total_amount,
                'txn_id'      => $finalPayment->txn_id,
                'type'        => 'stripe',
                'text'        => __('messages.payment_transfer', [
                    'from'   => get_user_name($finalPayment->customer_id),
                    'to'     => get_user_name($providerId),
                    'amount' => number_format((float) $finalPayment->total_amount, 2),
                ]),
                'other_transaction_detail' => null,
            ]);

            // Update bid status
            $bid->status = ($type === 'advance') ? 'advance_paid' : 'remaining_paid';
            $bid->save();
        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing error: ' . $e->getMessage());
        }
    }
}


