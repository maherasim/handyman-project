<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionTransaction;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\Models\Plans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionTransactionController extends Controller
{
    /**
     * Display a listing of subscription transactions
     */
    public function index()
    {
        \Log::info('SubscriptionTransactionController::index called');
        return view('admin.subscription-transactions.index');
    }

    /**
     * Simple test method
     */
    public function test()
    {
        return response()->json([
            'message' => 'Controller is working!',
            'timestamp' => now(),
            'transactions_count' => SubscriptionTransaction::count()
        ]);
    }

    /**
     * Get subscription transactions data for DataTable
     */
    public function indexData(Request $request)
    {
        \Log::info('SubscriptionTransactionController::indexData called');
        \Log::info('Request data:', $request->all());
        
        try {
            // Get all transactions with relationships
            $transactions = SubscriptionTransaction::with(['user', 'subscription'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('Found transactions:', ['count' => $transactions->count()]);
            
            $data = [];
            foreach ($transactions as $transaction) {
                $user = $transaction->user;
                $subscription = $transaction->subscription;
                
                $data[] = [
                    'id' => $transaction->id,
                    'user_name' => $user ? ($user->first_name . ' ' . $user->last_name) : 'Unknown User',
                    'user_email' => $user ? $user->email : 'No Email',
                    'plan_name' => $subscription ? $subscription->title : 'Unknown Plan',
                    'amount' => '€' . number_format($transaction->amount, 2),
                    'payment_type' => ucfirst(str_replace('_', ' ', $transaction->payment_type)),
                    'payment_status' => $transaction->payment_status,
                    'txn_id' => $transaction->txn_id,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'actions' => $transaction->id
                ];
            }

            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading subscription transactions: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Error loading data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Debug method to check data
     */
    public function debug()
    {
        $transactions = SubscriptionTransaction::all();
        $bankTransfers = SubscriptionTransaction::where('payment_type', 'bank_transfer')->get();
        $pending = SubscriptionTransaction::where('payment_status', 'pending')->get();
        
        return response()->json([
            'total_transactions' => $transactions->count(),
            'bank_transfers' => $bankTransfers->count(),
            'pending_transactions' => $pending->count(),
            'all_transactions' => $transactions->toArray(),
            'bank_transfer_transactions' => $bankTransfers->toArray(),
            'pending_transactions' => $pending->toArray()
        ]);
    }

    /**
     * Verify a bank transfer payment
     */
    public function verifyPayment(Request $request, $id)
    {
        try {
            $transaction = SubscriptionTransaction::findOrFail($id);
            
            if ($transaction->payment_status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction is not pending verification.'
                ]);
            }

            // Update transaction status
            $transaction->payment_status = 'paid';
            $transaction->save();

            // Update subscription status
            $subscription = ProviderSubscription::find($transaction->subscription_plan_id);
            if ($subscription) {
                $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                $subscription->save();

                // Update user subscription status
                $user = User::find($subscription->user_id);
                if ($user) {
                    $user->is_subscribe = 1;
                    $user->save();

                    // Send confirmation email
                    sendSubscriptionUpgradeEmail($user, $subscription, 'bank_transfer', $transaction->txn_id);
                }
            }

            Log::info('Bank transfer payment verified by admin', [
                'transaction_id' => $transaction->id,
                'user_id' => $subscription->user_id ?? null,
                'admin_id' => 'system'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment verified successfully. Subscription activated.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to verify payment: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to verify payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a bank transfer payment
     */
    public function rejectPayment(Request $request, $id)
    {
        try {
            $transaction = SubscriptionTransaction::findOrFail($id);
            
            if ($transaction->payment_status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction is not pending verification.'
                ]);
            }

            // Update transaction status
            $transaction->payment_status = 'rejected';
            $transaction->save();

            // Update subscription status
            $subscription = ProviderSubscription::find($transaction->subscription_plan_id);
            if ($subscription) {
                $subscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
                $subscription->save();
            }

            Log::info('Bank transfer payment rejected by admin', [
                'transaction_id' => $transaction->id,
                'user_id' => $subscription->user_id ?? null,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment rejected successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject payment: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to reject payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for subscription transactions
     */
    public function statistics()
    {
        $pending = SubscriptionTransaction::where('payment_status', 'pending')->count();
        $paid = SubscriptionTransaction::where('payment_status', 'paid')->count();
        $rejected = SubscriptionTransaction::where('payment_status', 'rejected')->count();
        $totalAmount = SubscriptionTransaction::where('payment_status', 'paid')->sum('amount');

        return response()->json([
            'pending' => $pending,
            'paid' => $paid,
            'rejected' => $rejected,
            'total_amount' => number_format($totalAmount, 2)
        ]);
    }

    /**
     * Bulk action for transactions
     */
    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'status' => false,
                'message' => 'No transactions selected.'
            ]);
        }

        try {
            switch ($action) {
                case 'verify':
                    $count = 0;
                    foreach ($ids as $id) {
                        $transaction = SubscriptionTransaction::find($id);
                        if ($transaction && $transaction->payment_status === 'pending') {
                            $transaction->payment_status = 'paid';
                            $transaction->save();

                            $subscription = ProviderSubscription::find($transaction->subscription_plan_id);
                            if ($subscription) {
                                $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                                $subscription->save();

                                $user = User::find($subscription->user_id);
                                if ($user) {
                                    $user->is_subscribe = 1;
                                    $user->save();
                                }
                            }
                            $count++;
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => "Successfully verified {$count} payments."
                    ]);

                case 'reject':
                    $count = 0;
                    foreach ($ids as $id) {
                        $transaction = SubscriptionTransaction::find($id);
                        if ($transaction && $transaction->payment_status === 'pending') {
                            $transaction->payment_status = 'rejected';
                            $transaction->save();

                            $subscription = ProviderSubscription::find($transaction->subscription_plan_id);
                            if ($subscription) {
                                $subscription->status = config('constant.SUBSCRIPTION_STATUS.INACTIVE');
                                $subscription->save();
                            }
                            $count++;
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => "Successfully rejected {$count} payments."
                    ]);

                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid action.'
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
