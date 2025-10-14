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
        return view('admin.subscription-transactions.index');
    }

    /**
     * Get subscription transactions data for DataTable
     */
    public function indexData(Request $request)
    {
        $query = SubscriptionTransaction::with(['user', 'subscription'])
            ->orderBy('created_at', 'desc');

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type !== '') {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $user = $transaction->user;
            $subscription = $transaction->subscription;
            
            $data[] = [
                'id' => $transaction->id,
                'user_name' => $user ? $user->first_name . ' ' . $user->last_name : 'N/A',
                'user_email' => $user ? $user->email : 'N/A',
                'plan_name' => $subscription ? $subscription->title : 'N/A',
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
                'admin_id' => auth()->id()
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
