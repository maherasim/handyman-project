<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

use App\Models\TransactionRequest;


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
        ->addColumn('check', fn($row) => '<input type="checkbox" class="table-checkbox" name="ids[]" value="' . $row->id . '">')
        ->addColumn('user_id', fn ($row) => optional($row->user)->name ?? 'N/A')
        ->addColumn('transaction_type', fn ($row) => ucfirst($row->transaction_type))
        ->addColumn('amount', fn ($row) => number_format($row->amount, 2))
        ->addColumn('status', fn ($row) => ucfirst($row->status))
        ->addColumn('action', function ($row) {
            return '<a href="/transaction-request/' . $row->id . '/edit" class="btn btn-sm btn-primary">Edit</a>
                    <a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-transaction">Delete</a>';
        })
        ->rawColumns(['check', 'action']) // <== Important: let HTML render
        ->make(true);
}




}
