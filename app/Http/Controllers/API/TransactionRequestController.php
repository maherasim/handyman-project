<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use App\Models\TransactionRequest;
use App\Models\Wallet;
use App\Models\WalletHistory;


class TransactionRequestController extends Controller
{
     public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed',
            'transaction_type' => 'required|string|max:255',
        ]);

        $transaction = TransactionRequest::create($validated);

        return response()->json([
            'message' => __('messages.transaction_request_sent'),
            'data' => $transaction,
        ], 201);
    }
     public function getByUser($user_id)
    {
        $transactions = TransactionRequest::where('user_id', $user_id)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.no_transactions_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

   public function index(Request $request){
        $filter = [
            'payment_status' => $request->payment_status,
        ];
        $pageTitle = __('messages.Transaction Request' );
        $assets = ['datatable'];
        return view('transaction.index', compact('pageTitle','assets','filter'));
    }
   public function walletindex(Request $request){
        $filter = [
            'payment_status' => $request->payment_status,
        ];
        $pageTitle = __('messages.Wallet Balance' );
        $assets = ['datatable'];
        $walletBalance = Wallet::where('user_id', auth()->id())->value('amount') ?? 0;
        return view('transaction.wallet_balance', compact('pageTitle','assets','filter','walletBalance'));
    }



    public function bulk_action(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', $request->rowIds)));
        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';
        switch ($actionType) {
            case 'change-status':
                $newStatus = $request->status;
                $transactions = TransactionRequest::whereIn('id', $ids)->get();
                \DB::beginTransaction();
                try {
                    foreach ($transactions as $transaction) {
                        $wasPending = $transaction->status === 'pending';
                        $transaction->status = $newStatus;
                        $transaction->save();
                        if ($newStatus === 'completed' && $wasPending) {
                            $userId = (int) $transaction->user_id;
                            $amount = (float) $transaction->amount;
                            $wallet = Wallet::firstOrCreate(
                                ['user_id' => $userId],
                                ['amount' => 0, 'status' => 1, 'title' => 'Wallet']
                            );
                            if ($wallet->amount === null) {
                                $wallet->update(['amount' => 0]);
                            }
                            $wallet->increment('amount', $amount);
                            $wallet->refresh();
                            WalletHistory::create([
                                'datetime'         => now(),
                                'user_id'          => $userId,
                                'activity_type'    => 'credit',
                                'activity_message' => 'Add wallet balance (Transaction Request #' . $transaction->id . ')',
                                'activity_data'    => json_encode([
                                    'credit_debit_amount' => $amount,
                                    'amount'              => $wallet->amount,
                                    'transaction_type'    => 'Credit',
                                    'transaction_request_id' => $transaction->id,
                                ]),
                            ]);
                        }
                    }
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    \Log::error('TransactionRequest bulk_action: ' . $e->getMessage());
                    return response()->json(['status' => false, 'message' => __('messages.failed_to_update')]);
                }
                $message = 'Bulk Payment Status Updated' . ($newStatus === 'completed' ? '. Balance added to wallets.' : '');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('messages.action_invalid')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

 
public function indexData(Request $request)
{
    $query = TransactionRequest::with('user');

    if ($request->has('filter.column_status') && $request->filter['column_status']) {
        $query->where('status', $request->filter['column_status']);
    }

    return DataTables::of($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="table-checkbox" name="ids[]" value="' . $row->id . '">';
        })
        ->addColumn('user_id', function ($row) {
            return optional($row->user)->username ?? 'N/A';
        })
        ->addColumn('transaction_type', function ($row) {
            return ucfirst($row->transaction_type);
        })
        ->addColumn('amount', function ($row) {
            return number_format($row->amount, 2);
        })
        ->addColumn('status', function ($row) {
            $badgeClass = $row->status === 'pending' ? 'bg-warning text-dark' : 'bg-success';
            $slug = strtolower((string) $row->status);
            $key = 'messages.' . $slug;
            $label = \Illuminate\Support\Facades\Lang::has($key) ? __($key) : ucfirst((string) $row->status);
            return '<span class="badge ' . $badgeClass . '">' . e($label) . '</span>';
        })
        ->addColumn('created_at', function ($row) {
            return \Carbon\Carbon::parse($row->created_at)->toDateString(); // e.g., 2025-07-03
        })
        ->addColumn('action', function ($row) {
            $disabled = $row->status === 'completed' ? 'disabled' : '';
            return '<button class="btn btn-sm btn-success confirm-btn" data-id="' . $row->id . '" ' . $disabled . '>Confirm Request</button>';
        })
        ->rawColumns(['check', 'status', 'action'])
        ->make(true);
}

public function walletindexData(Request $request)
{
    $query = Wallet::with('user');

    if ($request->has('filter.column_status') && $request->filter['column_status']) {
        $query->where('status', $request->filter['column_status']);
    }

    return DataTables::of($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="table-checkbox" name="ids[]" value="' . $row->id . '">';
        })
        ->addColumn('user_id', function ($row) {
            return optional($row->user)->username ?? 'N/A';
        })
        
        ->addColumn('amount', function ($row) {
            return number_format($row->amount, 2);
        })
                ->addColumn('status', function ($row) {
                if ($row->status == '0') {
                    return '<span class="badge bg-warning text-dark">' . e(__('messages.inactive')) . '</span>';
                }
                return '<span class="badge bg-success">' . e(__('messages.active')) . '</span>';
            })

        ->addColumn('created_at', function ($row) {
            return \Carbon\Carbon::parse($row->created_at)->toDateString(); // e.g., 2025-07-03
        })
      
        ->rawColumns(['check', 'status', 'action'])
        ->make(true);
}




public function confirmSingle($id)
{
    $transaction = TransactionRequest::findOrFail($id);

    if ($transaction->status === 'completed') {
        return response()->json(['message' => __('messages.request_already_confirmed')]);
    }

    \DB::beginTransaction();
    try {
        $transaction->status = 'completed';
        $transaction->save();

        $userId = (int) $transaction->user_id;
        $amount = (float) $transaction->amount;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['amount' => 0, 'status' => 1, 'title' => 'Wallet']
        );
        if ($wallet->amount === null) {
            $wallet->update(['amount' => 0]);
        }
        $wallet->increment('amount', $amount);
        $wallet->refresh();

        WalletHistory::create([
            'datetime'        => now(),
            'user_id'         => $userId,
            'activity_type'   => 'credit',
            'activity_message' => 'Add wallet balance (Transaction Request #' . $transaction->id . ')',
            'activity_data'   => json_encode([
                'credit_debit_amount' => $amount,
                'amount'              => $wallet->amount,
                'transaction_type'    => 'Credit',
                'transaction_request_id' => $transaction->id,
            ]),
        ]);

        \DB::commit();
        return response()->json(['message' => __('messages.request_confirmed_wallet')]);
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('TransactionRequest confirmSingle: ' . $e->getMessage());
        return response()->json(['message' => __('messages.failed_to_confirm_request')], 500);
    }
}




}
