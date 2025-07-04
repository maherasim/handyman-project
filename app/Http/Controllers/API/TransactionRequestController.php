<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use App\Models\TransactionRequest;
use App\Models\Wallet;


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
            'message' => 'Transaction request sent successfully.',
            'data' => $transaction,
        ], 201);
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
        return view('transaction.wallet_balance', compact('pageTitle','assets','filter'));
    }



    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';
        switch ($actionType) {
            case 'change-status':
                $branches = TransactionRequest::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Payment Status Updated';
                break;

            

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
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
            return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
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
    $query = Wallet::query();

    if ($request->has('filter.column_status') && $request->filter['column_status']) {
        $query->where('status', $request->filter['column_status']);
    }

    return DataTables::of($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="table-checkbox" name="ids[]" value="' . $row->id . '">';
        })
        ->addColumn('user_id', function ($row) {
            return $row->user_id ?? 'N/A';
        })
        ->addColumn('amount', function ($row) {
            return number_format($row->amount, 2);
        })
        ->addColumn('status', function ($row) {
            if ($row->status == '0') {
                return '<span class="badge bg-warning text-dark">Inactive</span>';
            }
            return '<span class="badge bg-success">Active</span>';
        })
        ->addColumn('created_at', function ($row) {
            return \Carbon\Carbon::parse($row->created_at)->toDateString();
        })
        ->rawColumns(['check', 'status'])
        ->make(true);
}




public function confirmSingle($id)
{
    $transaction = TransactionRequest::findOrFail($id);
    $transaction->status = 'completed';
    $transaction->save();

    return response()->json(['message' => 'Request confirmed successfully.']);
}




}
