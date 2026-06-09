<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletHistory;
use App\Models\Wallet;
use App\Http\Resources\API\WalletHistoryResource;
use App\Http\Resources\API\WalletResource;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use App\Models\WithdrawMoney;
use App\Models\PaymentGateway;
use App\Models\User;
class WalletController extends Controller
{
    use NotificationTrait;
    public function getHistory(Request $request)
    {
        $user_id = $request->user_id ?? auth()->user()->id;

        $wallet_history = WalletHistory::with('providers')->where('user_id', $user_id);
        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby : 'asc';

        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $wallet_history->count();
            }
        }
        
        $wallet_history = $wallet_history->orderBy('id', $orderBy)->paginate($per_page);
        $items = WalletHistoryResource::collection($wallet_history);
        $wallet_balance = Wallet::where('user_id', $user_id)->value('amount');
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
            'available_balance' => $wallet_balance,
        ];

        return comman_custom_response($response);
    }

    public function walletTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required',

        ]);

        $user_id = $request->user_id ?? auth()->user()->id;

        $wallet = Wallet::where('user_id', $user_id)->first();
    
        if (!$wallet) {
            $user = User::where('id', $user_id)->first();

            if ($user && ($user->user_type == 'user' || $user->user_type == 'provider')) {
                $wallet = Wallet::create([
                    'title' => $user->display_name,
                    'user_id' => $user->id,
                    'amount' => 0,
                ]);
            } else {
                return comman_custom_response(['error' => 'User not found or invalid user type']);
            }
        }
        
        $wallet->amount += $request->amount;
        
        $wallet->save();

        $activity_data = [
            'activity_type' => 'wallet_top_up',
            'wallet' => $wallet,
            'top_up_amount' => $request->amount,
            'transaction_type' => $request->transaction_type,
            'transaction_id' => $request->transaction_id,
        ];

        $this->sendNotification($activity_data);

        $response = [
            'message' => trans('messages.wallet_top_up', ['amount' => getPriceFormat($wallet->amount)]),
            'data' => $wallet,
        ];

        return comman_custom_response($response);
    }


    public function getwalletlist(Request $request)
    {
        $wallet = Wallet::query();

        if ($request->has('status') && !empty($request->status)) {

            $wallet = $wallet->where('status', $status);
        }

        $per_page = config('constant.PER_PAGE_LIMIT');

        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $wallet->count();
            }
        }

        $wallet = $wallet->orderBy('updated_at', 'desc')->paginate($per_page);
        $items = WalletResource::collection($wallet);

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
    public function store(Request $request)
    {

        if (demoUserPermission()) {
            $message = __('messages.demo_permission_denied');
            return comman_message_response($message);
        }
        $data = $request->all();

        $wallet = Wallet::where('user_id', $data['user_id'])->first();
        if ($wallet && !$data['id']) {
            $message = __('messages.already_provider_wallet');
            return comman_message_response($message, 406);
        }
        if ($wallet !== null) {
            $data['amount'] = $wallet->amount + $request->amount;
        }
        $result = Wallet::updateOrCreate(['id' => $data['id']], $data);


        $message = trans('messages.update_form', ['form' => trans('messages.wallet')]);
        if ($result->wasRecentlyCreated) {
            $activity_data = [
                'activity_type' => 'add_wallet',
                'wallet' => $result,
            ];
            $this->sendNotification($activity_data);

            $message = trans('messages.save_form', ['form' => trans('messages.wallet')]);
        } else {
            if ($wallet->amount  != $data['amount']) {
                $activity_data = [
                    'activity_type' => 'update_wallet',
                    'wallet' => $result,
                    'added_amount' => $request->amount
                ];
                $this->sendNotification($activity_data);
            }
        }

        return comman_message_response($message);
    }

public function withdarawMoney(Request $request)
{
    try {
        if (auth()->user()->hasRole('user')) {
            return comman_message_response('Not allowed.', 403);
        }

        $data = $request->except('_token');

        $user_id = $data['user_id'];
        $wallet = Wallet::where('user_id', $user_id)->first();

        if (!$wallet) {
            return comman_message_response('Wallet not found for user.', 404);
        }

        if ($wallet->amount < $data['amount']) {
            return comman_message_response('Insufficient balance to withdraw.', 400);
        }

        // Validate required fields
        if (!isset($data['bank']) || !isset($data['amount']) || !isset($data['payment_method'])) {
            return comman_message_response('Missing required fields.', 422);
        }

        if ($data['payment_method'] === 'bank') {
            // ✅ Manual bank withdrawal flow
            $data['bank_id'] = $data['bank']; // assuming this is bank.id from your banks table
            $data['payment_type'] = 'manual'; // You can name it 'bank_transfer' or 'manual'
            $data['datetime'] = Carbon::now();
            $data['status'] = 'pending'; // Admin will process this manually

            $withdraw = WithdrawMoney::create($data);

            // Deduct wallet balance
            $wallet->amount -= $data['amount'];
            $wallet->save();

            // Log activity (optional)
            $activity_data = [
                'id' => $withdraw->id,
                'type' => 'wallet',
                'wallet' => $wallet,
                'activity_type' => 'withdraw_money',
                'user_id' => $user_id,
                'amount' => $data['amount'],
            ];
            $this->sendNotification($activity_data);

            // Response
            $message = __('messages.withdrawal_requested', ['amount' => $data['amount']]);
            return comman_message_response($message, 200);
        }

        return comman_message_response('Invalid payment method.', 400);
    } catch (\Exception $e) {
        \Log::error('WithdrawMoney Error: ' . $e->getMessage(), [
            'request' => $request->all(),
            'trace' => $e->getTraceAsString(),
        ]);

        return comman_message_response('Something went wrong. Please contact support.', 500);
    }
}




}
