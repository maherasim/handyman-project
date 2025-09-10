<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\PaymentHistory;
use Yajra\DataTables\DataTables;
use App\Traits\NotificationTrait;
use App\Models\Setting;
use App\Models\Country;
use App\Models\Payment;
use App\Models\WithdrawMoney;
use Carbon\Carbon;
use App\Models\User;

class WalletController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         dd($request->all());
        $filter = [ 
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',["form" => __('messages.wallet')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        $walletBalance = Wallet::where('user_id', auth()->id())->value('amount') ?? 0;
        return view('wallet.index', compact('pageTitle','auth_user','assets','filter','walletBalance'));
    }
    public function cashIndex($id)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.cash_history')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('paymenthistory.index', compact('pageTitle','assets','auth_user','id'));
    }


    public function index_data(DataTables $datatable, Request $request)
    {
        // Get the current logged-in user
        $user = auth()->user();
    
        // Start the Wallet query
        $query = Wallet::query();
    
        // If the user is not an admin, filter the query to show only their wallet
        if (!$user->hasAnyRole(['admin'])) {
            $query->where('user_id', $user->id);
        }
    
        // Apply filters from the request
        $filter = $request->filter;
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
    
        // Order by the most recent update
        $query->orderBy('updated_at', 'desc');
    
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('title', function ($query) {
                return '<a class="btn-link btn-link-hover" href=' . route('wallet.show', $query->user_id) . '>' . $query->title . '</a>';
            })
            ->editColumn('user_id', function ($query) {
                return view('wallet.user', compact('query'));
            })
            ->editColumn('amount', function ($query) {
                return getPriceFormat($query->amount);
            })
            ->editColumn('status', function ($query) {
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input change_status" data-type="wallet_status" ' . ($query->status ? "checked" : "") . ' value="' . $query->id . '" id="' . $query->id . '" data-id="' . $query->id . '">
                        <label class="custom-control-label" for="' . $query->id . '" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->addColumn('action', function ($wallet) {
                return view('wallet.action', compact('wallet'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title', 'action', 'status', 'check'])
            ->toJson();
    }
    
    
    public function cash_index_data(DataTables $datatable,Request $request)
    {
         $query = Payment::query()->myPayment()->where('payment_type', 'wallet');
        
       dd($query ); 
        $filter = $request->filter;
 
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('payment_status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query= $query->where('payment_type','wallet')->where('status','0')->newQuery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
            ->editColumn('id', function($payment) {
                if(isset($payment->booking) && $payment->booking !== null){
                    return '<a class="btn-link btn-link-hover" href='.route('booking.show', $payment->booking->id).'> #'.$payment->booking->id.'</a>';
                }
            })
            ->editColumn('booking_id', function($payment) {
                if(!empty($payment->booking->bookingPackage)){
                    $service_name = optional(optional($payment->booking)->bookingPackage)->name." (".__('messages.service_package').")";
                }
                else{
                    $service_name = optional(optional($payment->booking)->service)->name." (".__('messages.service').")";
                }
     
                return ($payment->customer_id != null &&isset($payment->booking->service)) ? $service_name :'-';
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
            ->editColumn('datetime' , function ($query){
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                $date = date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
                return $date;
            })
            ->editColumn('total_amount', function($payment) {
                return getPriceFormat($payment->total_amount);
            })
            ->editColumn('history', function($payment) {
                $action = '<a class="btn-link btn-link-hover" href="'.route('wallet2.index', $payment->id).'">'.__('messages.view').'</a>';
                return $action;
            })
            ->editColumn('status', function($query) {
                $payment = $query->payment_status;
                if($payment !== null){
                    $payment_status = '<span class="text-center bg badge-primary">'.str_replace('_'," ",ucfirst($payment)).'</span>';
                }else{
                    $payment_status = '<span class="text-center d-block">-</span>';
                }
                return $payment_status;
            })

            ->editColumn('action', function($payment) {
                $action=null;
               if(auth()->user()->hasRole(['admin']) || auth()->user()->hasRole(['demo_admin']) ){
                // $action = set_admin_approved_cash($payment->id). ' ' .view('payment.cashaction',compact('payment'))->render();
                $action = view('payment.cashaction',compact('payment'))->render();
               }
              
                return $action;

            })
            ->addIndexColumn()->rawColumns(['check','history','action','id','status'])
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
                $branches = Wallet::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Wallet Status Updated';
                break;

            case 'delete':
                Wallet::whereIn('id', $ids)->delete();
                $message = 'Bulk Wallet Deleted';
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
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $wallet = Wallet::find($id);
        $pageTitle = trans('messages.update_form_title',['form'=>trans('messages.wallet')]);

        if($wallet == null){
            $pageTitle = trans('messages.add_button_form',['form' => trans('messages.wallet')]);
            $wallet = new Wallet;
        }

        return view('wallet.create', compact('pageTitle' ,'wallet' ,'auth_user' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)
{
    try {
        // Validate input
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'activity_message' => 'nullable|string|max:255',
        ]);

        // Check if wallet already exists for the user
        $wallet = Wallet::where('user_id', $validated['user_id'])->first();
        $wallet->new_amount = $validated['amount'];

        if ($wallet) {
            // Update existing wallet amount
            $wallet->amount += $validated['amount'];
            $wallet->status = $validated['status'];
            $wallet->save();
        } else {
            // Create new wallet
            $wallet = Wallet::create([
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'status' => $validated['status'],
            ]);
        }

        // Optional log activity
        if (!empty($validated['activity_message'])) {
            \Log::info('Wallet Stored', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'activity_message' => $validated['activity_message'],
            ]);
        }

        return redirect()->route('wallet_balance.index')->with('success', 'Wallet saved successfully.');

    } catch (\Exception $e) {
        \Log::error('Wallet Store Error: ' . $e->getMessage(), [
            'request' => $request->all()
        ]);

        return redirect()->back()->with('error', 'Failed to save wallet. ' . $e->getMessage());
    }
}



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=User::where('id',$id)->first();
        $username=$user->display_name ?? null;
        $pageTitle = __('messages.wallet_history',['user_name' =>$username] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('wallet.view', compact('pageTitle','auth_user','assets','id'));
    }

public function wallethistory_index_data(DataTables $datatable, $id)
{
    $user = auth()->user();
    $query = null;

    if ($user->user_type === 'user') {
        $query = WalletHistory::where('user_id', $id)->orderBy('id', 'desc');

        return $datatable->eloquent($query)
            ->editColumn('user_id', function ($history) {
                return ($history->user_id != null && isset($history->providers)) ? $history->providers->display_name : '-';
            })
            ->editColumn('activity_type', function ($history) {
                return $history->activity_type ? str_replace("_", " ", ucfirst($history->activity_type)) : '-';
            })
            ->addIndexColumn()
            ->toJson();

    } elseif ($user->user_type === 'provider') {
        $query = PaymentHistory::where('receiver_id', $id)->where('type','wallet')->orderBy('id', 'desc');

        return $datatable->eloquent($query)
            ->editColumn('receiver_id', function ($history) {
                return ($history->receiver_id != null && isset($history->receiver)) ? $history->receiver->display_name : '-';
            })
            ->editColumn('payment_type', function ($history) {
                return $history->payment_type ? str_replace("_", " ", ucfirst($history->payment_type)) : '-';
            })
            ->addIndexColumn()
            ->toJson();
    }

    abort(403, 'Unauthorized access');
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
        $wallet = Wallet::find($id);
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.wallet')] );

        if($wallet != '') {
            $wallet->delete();
            $msg= __('messages.wallet_deleted');
        }
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

public function getWalletPaymentMethod(Request $request)
{
    $data = $request->all();
    $data['datetime'] = now();
    $data['payment_status'] = 'failed';

    $payment_data = Payment::where('booking_id', $data['booking_id'])->first();

    if (!empty($payment_data)) {
        $payment_data->update($data);
    } else {
        $payment_data = Payment::create($data);
    }

    $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
    $country_id = $sitesetupdata['default_currency'] ?? null;
    $country = Country::find($country_id);

    $data['currency_code'] = $country ? $country->currency_code : "USD";

    switch ($data['payment_type']) {
        case 'stripe':
            $data['payment_geteway_data'] = getPaymentMethodkey($data['payment_type']);
            break;

        case 'paypal':
            // Add PayPal gateway info, e.g. client ID, secret or config
            $data['payment_geteway_data'] = getPaymentMethodkey($data['payment_type']); // You should implement this for PayPal
            break;

        default:
            // other types like wallet, bank transfer etc.
            break;
    }

    return comman_custom_response($data);
}

    public function createWalletStripePayment(Request $request)
    {

        $data = $request->all();

        $checkout_session = addWalletAmount($data);
        if(isset($checkout_session['message'])) {

            return comman_custom_response($checkout_session);

        }else{
            Payment::where('booking_id', $data['booking_id'])->update(['other_transaction_detail' => $checkout_session['id']]);

            return comman_custom_response($checkout_session);
       }

    }
    public function saveWalletStripePayment(Request $request, $id){


        $request->validate([
            'amount' => 'required|integer',
        ]);

        $user_id = $id ?? auth()->user()->id;

        $wallet = Wallet::where('user_id', $user_id)->first();

        if($wallet){

            $wallet->amount += $request->amount;
            $wallet->save();

            $activity_data = [

                'activity_type' => 'wallet_top_up',
                'wallet' => $wallet,
                'top_up_amount' =>$wallet->amount,
                'transaction_type'=> $wallet->transcation_type,
                'transaction_id'=> $wallet->transcation_id,

            ];
            $this->sendNotification($activity_data);

            return redirect('/booking-list');

          }

     }

    public function wallet_transaction_index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',['form' => __('messages.provider_withdrawal_requests')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('wallet.transaction_index', compact('pageTitle','auth_user','assets','filter'));
    }

    public function wallet_transaction_index_data(DataTables $datatable,Request $request)
    {
        $query = WithdrawMoney::query()->with('providers','bank');

        if(auth()->user()->hasRole('provider')){
            $query = $query->where('user_id', auth()->user()->id);
        }

        $filter = $request->filter;
        $query = $query->orderBy('updated_at','desc');
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
            ->editColumn('user_id' , function ($query){
                return view('wallet.user', compact('query'));
            })

            ->filterColumn('user_id',function($query,$keyword){
                $query->whereHas('providers',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->orderColumn('user_id', function ($query, $order) {
                $query->select('withdraw_money.*')
                      ->join('users as customers', 'customers.id', '=', 'withdraw_money.user_id')
                      ->orderBy('customers.display_name', $order);   
            })
            ->editColumn('bank_id', function($query) {
                $bankName = '';
                if($query->payment_type == 'manual'){
                    $bankName = __('messages.manual_transaction');
                }
                else{
                    $bankName = optional($query->bank)->bank_name;
                    $accountNo = optional($query->bank)->account_no;
                    if ($accountNo) {
                        $maskedAccountNo = str_repeat('X', strlen($accountNo) - 4) . substr($accountNo, -4);
                        return '<b>' . $bankName.'</b>'  . '<br>' . $maskedAccountNo . '</br>';
                    }
                }
                return $bankName;
            })
            ->orderColumn('bank_id', function ($query, $order) {
                $query->select('withdraw_money.*')
                      ->join('banks', 'banks.id', '=', 'withdraw_money.bank_id')
                      ->orderBy('banks.bank_name', $order);   
            })
            
            ->editColumn('amount' , function ($query){
                return getPriceFormat($query->amount);
            })
            ->editColumn('payment_type' , function ($query){
                return ucfirst($query->payment_type);
            })
            ->editColumn('user_type' , function ($query){
                return optional($query->providers)->user_type ?? '-';
            })
            ->filterColumn('user_type',function($query,$keyword){
                $query->whereHas('providers',function ($q) use($keyword){
                    $q->where('user_type','like','%'.$keyword.'%');
                });
            })
            ->editColumn('datetime' , function ($query){
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                $date = optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->datetime)) .'  '. date(optional($datetime)->time_format, strtotime($query->datetime))
                : $query->datetime;

                return $date;

            })
            ->editColumn('status' , function ($query){
                if($query->status == 'paid'){
                    $status = '<span class="badge badge-active text-success bg-success-subtle">'.ucfirst($query->status).'</span>';
                }else{
                    $status = '<span class="badge badge-inactive text-danger bg-danger-subtle">'.ucfirst($query->status).'</span>';
                }
                return $status;
            })
            ->addColumn('action', function($query){
                $allData = WithdrawMoney::all();
                $exists = $allData->contains('withdraw_money_id', $query->id);
                return view('wallet.transaction_action',compact('query','exists'))->render();
                
            })
            ->addIndexColumn()
            ->rawColumns(['user_id','bank_id','amount','datetime','action','status','check'])
            ->toJson();
    }

   public function wallet_transaction_payout($id)
{
    $withdraw_money = WithdrawMoney::where('id', $id)->first();

    if ($withdraw_money) {
        $withdraw_money->status = 'paid';
        $withdraw_money->save();
    }

    $message = __('messages.transaction_complete_success');
    return redirect(route('wallet_transaction'))->withSuccess($message);
}

}
