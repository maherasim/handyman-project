<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PaymentHistory;
use App\Models\Payment;
use App\Models\Setting;
use Facade\Ignition\DumpRecorder\Dump;
use Yajra\DataTables\DataTables;
use App\Models\Booking;
use App\Models\CommissionEarning;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'payment_status' => $request->payment_status,
        ];
        $pageTitle = __('messages.payments' );
        $assets = ['datatable'];

        return view('payment.index', compact('pageTitle','assets','filter'));
    }

    public function cashIndex($id)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.cash_history')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('paymenthistory.index', compact('pageTitle','assets','auth_user','id'));
    }

public function paymenthistory_index_data(DataTables $datatable, $id)
{
    $query = PaymentHistory::where('payment_id', $id);

    if (auth()->user()->hasAnyRole(['admin'])) {
        $query->newQuery();
    }

    return $datatable->eloquent($query)
        ->editColumn('sender_id', function ($payment) {
            return ($payment->sender != null && isset($payment->sender)) ? $payment->sender->display_name : '-';
        })
        ->filterColumn('sender_id', function ($query, $keyword) {
            $query->whereHas('sender', function ($q) use ($keyword) {
                $q->where('display_name', 'like', '%' . $keyword . '%');
            });
        })
        ->editColumn('receiver_id', function ($payment) {
            return ($payment->receiver != null && isset($payment->receiver)) ? $payment->receiver->display_name : '-';
        })
        ->filterColumn('receiver_id', function ($query, $keyword) {
            $query->whereHas('receiver', function ($q) use ($keyword) {
                $q->where('display_name', 'like', '%' . $keyword . '%');
            });
        })
        ->editColumn('datetime', function ($query) {
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            return date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
        })
        ->addColumn('created_at', function ($payment) {
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            return date("$datetime->date_format $datetime->time_format", strtotime($payment->created_at));
        })
        ->addIndexColumn()
        ->toJson();
}


    public function cashDatatable(Request $request){
        $filter = [
            'payment_status' => $request->payment_status,
        ];
        $pageTitle = __('messages.cash_payments' );
        $assets = ['datatable'];
        return view('payment.cash', compact('pageTitle','assets','filter'));
    }

public function cash_index_data(DataTables $datatable, Request $request)
{
    $query = Payment::query()
        ->myPayment()
        ->where('payment_type', 'bank_transfer');

    // Only for admin, add status = 0
    if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
        $query->where('status', '0')->orderByDesc('id');
    }

    $filter = $request->filter;

    // Apply payment_status filter from frontend
    if (!empty($filter['column_status'])) {
        $query->where('payment_status', $filter['column_status']);
    }

    return $datatable->eloquent($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$row->id.'" name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
        })
        ->editColumn('id', function($payment) {
            if ($payment->booking) {
                return '<a class="btn-link btn-link-hover" href="'.route('booking.show', $payment->booking->id).'"> #'.$payment->booking->id.'</a>';
            }
            return '-';
        })
        ->editColumn('booking_id', function($payment) {
            return $payment->booking->service->name ?? '-';
        })
        ->filterColumn('booking_id', function($query, $keyword) {
            $query->whereHas('booking.service', function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%');
            });
        })
        ->editColumn('customer_id', function ($payment) {
            return view('payment.user', compact('payment'));
        })
        ->filterColumn('customer_id', function($query, $keyword) {
            $query->whereHas('customer', function ($q) use ($keyword) {
                $q->where('display_name', 'like', '%'.$keyword.'%');
            });
        })
        ->editColumn('datetime', function ($query) {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            return date("$datetime->date_format / $datetime->time_format", strtotime($query->datetime));
        })
        ->editColumn('total_amount', function($payment) {
            return getPriceFormat($payment->total_amount);
        })
        ->editColumn('history', function($payment) {
            return '<a class="btn-link btn-link-hover" href="'.route('cash.index', $payment->id).'">View</a>';
        })
        ->editColumn('status', function($query) {
            return $query->payment_status
                ? '<span class="text-center badge badge-primary1">'.str_replace('_', ' ', ucfirst($query->payment_status)).'</span>'
                : '<span class="text-center d-block">-</span>';
        })
        ->editColumn('action', function($payment) {
            if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
                return set_admin_approved_cash($payment->id) . ' ' . view('payment.cashaction', compact('payment'))->render();
            }
            return '';
        })
        ->addIndexColumn()
        ->rawColumns(['check', 'history', 'action', 'id', 'status'])
        ->toJson();
}




    public function index_data(DataTables $datatable,Request $request)
    {
      $query = Payment::query()->myPayment()
    ->where(function ($q) {
        $q->where('payment_type', '!=', 'bank_transfer')
          ->orWhere(function ($sub) {
              $sub->where('payment_type', 'bank_transfer')->where('status', 1);
          });
    });
 
        // if (!$request->order) { 
        //     $query->orderBy('created_at', 'DESC');
        // } 
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('payment_status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
         ->editColumn('id', function($query) {
    $booking = optional($query->booking);
    return $booking->id
        ? "<a class='btn-link btn-link-hover' href=" . route('booking.show', $booking->id) . ">#" . $booking->id . "</a>"
        : '-';
})

        ->orderColumn('id', function($query, $order) {
            $query->orderBy('payments.booking_id', $order);  
        })
        ->editColumn('booking_id', function($query) {
            if(!empty($query->booking->bookingPackage)){
                $service_name = optional(optional($query->booking)->bookingPackage)->name." (".__('messages.service_package').")";
            }
            else{
                $service_name = optional(optional($query->booking)->service)->name." (".__('messages.service').")";
            }
 
            return ($query->customer_id != null &&isset($query->booking->service)) ? $service_name :'-';
        })
        ->filterColumn('booking_id',function($query,$keyword){
            $query->whereHas('booking.service',function ($q) use($keyword){
                $q->where('name','like','%'.$keyword.'%');
            });
        })
        ->orderColumn('booking_id', function ($query, $order) {
            $query->join('bookings', 'bookings.id', '=', 'payments.booking_id')  
                  ->join('services', 'services.id', '=', 'bookings.service_id')  
                  ->orderBy('services.name', $order);  
        })
        ->editColumn('customer_id', function ($payment) {
            return view('payment.user', compact('payment'));
        })
        ->filterColumn('customer_id',function($query,$keyword){
            $query->whereHas('customer',function ($q) use($keyword){
                $q->where('display_name','like','%'.$keyword.'%');
            });
        })
        ->orderColumn('customer_id', function ($query, $order) {
            $query->select('payments.*')
                  ->join('users as customers', 'customers.id', '=', 'payments.customer_id')
                  ->orderBy('customers.display_name', $order);   
        })
        ->editColumn('datetime' , function ($query){
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            $date = date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
            return $date;
        })
        ->editColumn('payment_status', function($query) {
            $payment = $query->payment_status;
            if($payment !== null){
                $payment_status = '<span class="text-center text-white badge bg-primary">'.str_replace('_'," ",ucfirst($payment)).'</span>';
            }else{
                $payment_status = '<span class="text-center d-block">-</span>';
            }
            return $payment_status;
        })
        ->addColumn('handyman_earning', function($payment) {
            // Optional: eager load in controller for performance
            $earning = optional($payment->handymanEarning)->commission_amount;
            return getPriceFormat($earning ?? 0);
        })


        ->editColumn('total_amount', function($query) {
            return getPriceFormat($query->total_amount);
        })
        ->addColumn('action', function($payment){
            return view('payment.action',compact('payment'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['action','check','payment_status','id'])
            ->toJson();
    }






















    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';
        switch ($actionType) {
            case 'change-status':
                $branches = Payment::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Payment Status Updated';
                break;

            case 'delete':
                Payment::whereIn('id', $ids)->delete();
                $message = 'Bulk Payment Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $document = Payment::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.payment')] );

        if( $document!='') {

            $document->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.payment')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

    public function cashApprove($id){

        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $admin = json_decode($sitesetup->value);
        $paymentdata = Payment::where('id',$id)->first();
        $parent_payment_history = PaymentHistory::where('status','pending_by_admin')
        ->where('payment_id',$id)->first();

        $payment_history = [
            'payment_id' => $id,
            'booking_id' => $paymentdata->booking_id,
            'action' => config('constant.PAYMENT_HISTORY_ACTION.ADMIN_APPROVED_CASH'),
            'type' => $parent_payment_history->type,
            'sender_id' => $parent_payment_history->sender_id,
            'receiver_id' => admin_id(),
            'total_amount' => $paymentdata->total_amount,
            'text' =>  __('messages.cash_approved',['amount' => getPriceFormat((float)$paymentdata->total_amount),'name' => get_user_name(admin_id())]),
            'status' => config('constant.PAYMENT_HISTORY_STATUS.APPROVED_ADMIN'),
            'parent_id' => $parent_payment_history->parent_id
        ];

     
        date_default_timezone_set( $admin->time_zone ?? 'UTC');
        $payment_history['datetime'] = date('Y-m-d H:i:s');

        if(!empty($paymentdata->txn_id)){
            $payment_history['txn_id'] =$paymentdata->txn_id;
        }
        if(!empty($paymentdata->other_transaction_detail)){
            $payment_history['other_transaction_detail'] =$paymentdata->other_transaction_detail;
        }
        $res = PaymentHistory::create($payment_history);
        // $parent_record = PaymentHistory::where('parent_id',$parent_payment_history->parent_id)->first();

        // $payment_record_data= PaymentHistory::where('payment_id',$id)->update(['status'=>'approved_by_admin']);

        // $parent_record->status = 'approved_by_admin';
        // $parent_record->update();

        $booking = Booking::where('id', $payment_history['booking_id'])->first();

        if($booking->status == 'completed'){

            CommissionEarning::where('booking_id',$booking->id)->update(['commission_status'=>'unpaid']);
        }

        $paymentdata->payment_status = 'paid';
        $paymentdata->update();

        $msg = __('messages.approve_successfully');
        return redirect()->back()->withSuccess($msg);
    }

}
