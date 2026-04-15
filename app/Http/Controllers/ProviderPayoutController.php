<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderPayout;
use App\Models\Booking;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Bank;
use App\Models\WithdrawMoney;
use Carbon\Carbon;
use App\Http\Requests\ProviderPayout as ProviderPayoutRequest;
use Yajra\DataTables\DataTables;
use App\Traits\NotificationTrait;
use App\Traits\EarningTrait;
use App\Models\CommissionEarning;
use App\Models\Setting;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class ProviderPayoutController extends Controller
{
    use NotificationTrait;
    use EarningTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Redirect to show method with current user's ID, or to earning page
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole(['provider', 'handyman'])) {
                // Resource route expects 'providerpayout' parameter, not 'id'
                return redirect()->route('providerpayout.show', ['providerpayout' => $user->id]);
            } elseif ($user->hasRole(['admin', 'demo_admin'])) {
                return redirect()->route('earning');
            }
        }
        return redirect()->route('home');
    }

    public function index_data(DataTables $datatable,Request $request)
    {
        $id = $request->id;
        $query = ProviderPayout::where('provider_id',$id);
        $filter = $request->filter;

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
        ->editColumn('payment_method', function($payout) {
            return !empty($payout->payment_method) ? ucfirst($payout->payment_method) : 'cash';
        })
        ->addColumn('bank_name', function($payout) {

            if($payout->payment_method == 'bank'){
                $bank = Bank::where('id',$payout->bank_id)->value('bank_name');
                return $bank;
            }
            else{
                return '-';
            }

            })

        ->editColumn('provider_id', function ($payout) {
            return view('providerpayout.user', compact('payout'));
        })

        ->filterColumn('provider_id',function($payout,$keyword){
            $payout->whereHas('providers',function ($q) use($keyword){
                $q->where('first_name','like','%'.$keyword.'%');
            });
        })
        ->editColumn('amount', function($payout) {
            return ($payout->amount != null && isset($payout->amount)) ? getPriceFormat($payout->amount) : '-';
        })
        ->editColumn('created_at', function($payout) {
            return $payout->created_at;
        })
        ->addColumn('action', function($providerpayout){
            return view('providerpayout.action',compact('providerpayout'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['check','title','action','status','bank_name'])
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
                $branches = ProviderPayout::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Provider Payout Status Updated';
                break;

            case 'delete':
                ProviderPayout::whereIn('id', $ids)->delete();
                $message = 'Bulk Provider Payout Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' =>$message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id,$redirect_type = null)
    {
        $auth_user = authSession();
        $pageTitle = trans('messages.add_button_form',['form' => trans('messages.provider_payout')]);
        $payoutdata = new ProviderPayout;

        $provider = User::with('providertype','commission_earning')->find($id);

        $commissionData = $provider->commission_earning()
            ->whereHas('getbooking', function ($query) {
                $query->where('status', 'completed');
            })
            ->where('commission_status', 'unpaid')
            ->where('user_type', 'provider')
            ->get(); // Use get() to retrieve the results as a collection

        $ProviderEarning = 0;

        if ($commissionData) {
            foreach ($commissionData as $commission) {
                $commission_data = CommissionEarning::where('booking_id', $commission->booking_id) // Use $commission->booking_id
                    ->whereIn('user_type', ['provider', 'handyman'])
                    ->where('commission_status', 'unpaid')
                    ->get(); 

                foreach ($commission_data as $data) {
                    if (isset($data->commission_amount)) {
                        $ProviderEarning += $data->commission_amount;
                    }
                }
            }
        }
        $setting = Setting::getValueByKey('site-setup','site-setup');
        $digitafter_decimal_point = $setting ? $setting->digitafter_decimal_point : "2";
        $total_earning = round($ProviderEarning,$digitafter_decimal_point);

        $payoutdata->amount = $total_earning;

        $payoutdata->provider_id = $id;

        // Fetch wallet balance for handyman and provider users
        $walletBalance = 0;
        $currentUser = auth()->user();
        if ($currentUser && in_array($currentUser->user_type, ['handyman', 'provider'])) {
            $wallet = Wallet::where('user_id', $currentUser->id)->first();
            $walletBalance = $wallet ? ($wallet->amount ?? 0) : 0;
        }

        return view('providerpayout.create', compact('pageTitle' ,'payoutdata' ,'auth_user' ,'redirect_type', 'walletBalance'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProviderPayoutRequest $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $admin = \App\Models\AppSetting::first();
        date_default_timezone_set( $admin->time_zone ?? 'UTC');
             $data = $request->except('_token');

             $payout_status='';

             // Ensure payment_method defaults to 'bank' if not provided
             if (!isset($data['payment_method']) || empty($data['payment_method'])) {
                 $data['payment_method'] = 'bank';
             }

             $payment_gateway = isset($data['payment_gateway']) ? $data['payment_gateway'] : ' ';

             $provider_id=$data['provider_id'];

            if($data['payment_method'] === 'bank'){

              switch($payment_gateway){

               case 'razorpayx':

                 $response=providerpayout_rezopayX($data);

                 if($response==''){

                    return redirect()->back()->withErrors(trans('messages.rezorpayx_details'))->withInput();

                  }

                 $payout_details = json_decode($response,True);
                 $payout = $payout_details;

                if($error=$payout['error']['description']  ==''){

                    $payout_id=$payout['id'] ;
                    $data['paid_date']=Carbon::now();

                  }else {

                     $razorpay_message=$payout['error']['description'];

                     return  redirect()->back()->withErrors(trans('messages.razorpay_message',['razorpay_message' => $razorpay_message]))->withInput();

                   }
               break;

               case 'stripe':

                $response=providerpayout_stripe($data);

               if($response==''){

                  return redirect()->back()->withErrors(trans('messages.stripe_details'))->withInput();

                 }else{

                 $status = $response->status;

               if($status==400){

                 $error_message = $response->code;

                 return  redirect()->back()->withErrors(trans('messages.stripe_message',['stripe_message' => $error_message]))->withInput();

                }else{



                 $payout_id=$response['id'];

                   $status='';

                   if($payout_id!=''){

                        $status="paid";
                    }

                     $data['bank_id']=$data['bank'];
                     $data['status']=$status;
                     $data['paid_date']=Carbon::now();

                 }
               }

                break;

            }

         }
        
        // If payment method is 'bank', save to withdraw_money table and deduct wallet immediately
        if($data['payment_method'] === 'bank') {
            $amount = (float) ($data['amount'] ?? 0);
            $wallet = Wallet::where('user_id', $provider_id)->first();
            if (!$wallet || $wallet->amount < $amount) {
                $message = __('messages.withdrawal_insufficient_balance');
                if ($request->is('api/*')) {
                    return comman_message_response($message, 400);
                }
                return redirect()->back()->withErrors($message)->withInput();
            }

            // Prepare data for withdraw_money table
            $withdrawData = [
                'user_id' => $provider_id,
                'amount' => $data['amount'],
                'bank_id' => $data['bank'] ?? null,
                'datetime' => Carbon::now(),
                'payment_type' => 'manual',
                'status' => 'pending',
            ];

            $created = [];
            DB::transaction(function () use ($withdrawData, $data, $provider_id, $amount, &$created) {
                $created['withdrawResult'] = WithdrawMoney::create($withdrawData);
                $created['result'] = ProviderPayout::create($data);
                $withdrawResult = $created['withdrawResult'];
                $result = $created['result'];
                if (isset($withdrawResult->id) && isset($result->id)) {
                    $withdrawResult->withdraw_money_id = $result->id;
                    $withdrawResult->save();
                }
                // Deduct from wallet immediately when request is submitted
                $wallet = Wallet::where('user_id', $provider_id)->first();
                if ($wallet) {
                    $wallet->decrement('amount', $amount);
                    WalletHistory::create([
                        'datetime'         => now(),
                        'user_id'          => $provider_id,
                        'activity_type'    => 'debit',
                        'activity_message' => __('messages.withdrawal_paid_activity', ['amount' => getPriceFormat($amount)]),
                        'activity_data'    => json_encode([
                            'credit_debit_amount' => $amount,
                            'amount'              => $wallet->fresh()->amount,
                            'transaction_type'    => 'Debit',
                            'withdraw_money_id'   => $withdrawResult->id,
                        ]),
                    ]);
                }
            });

            $withdrawResult = $created['withdrawResult'];
            $result = $created['result'];
            $wallet = Wallet::where('user_id', $provider_id)->first();

            $activity_data = [
                'type' => 'withdraw_money',
                'activity_type' => 'withdraw_money',
                'id' => $withdrawResult->id,
                'user_id' => $provider_id,
                'amount' => $data['amount'],
            ];
            if ($wallet) {
                $activity_data['wallet'] = $wallet;
            }
            $this->sendNotification($activity_data);

            CommissionEarning::where('employee_id', $provider_id)->where('commission_status','unpaid')->update(['commission_status' => 'paid']);

            $message = __('messages.withdrawal_request_submitted');
            if($request->is('api/*')){
                return comman_message_response($message);
            }
            Session::flash('success', $message);
            return redirect()->route('providerpayout.create', ['id' => $provider_id]);
        } else {
            // For other payment methods (wallet, cash), use the original flow
            $result = ProviderPayout::create($data);
        }

        CommissionEarning::where('employee_id', $provider_id)->where('commission_status','unpaid')->update(['commission_status' => 'paid']);
        $activity_data = [
            'type' => 'provider_payout',
            'activity_type' => 'provider_payout',
            'id' => $result->id,
            'pay_date' => $result->paid_date ?? null,
            'user_id' => $result->provider_id,
            'amount' => $result->amount,
        ];
        $this->sendNotification($activity_data);


        if($result){

            if($data['payment_method'] === 'wallet'){
                $wallet = Wallet::where('user_id',$data['provider_id'])->first();
                if($wallet){

                    $wallet_amount = $wallet->amount;
                    $payout_amount = $result->amount;
                    $final_wallet_amount = $wallet_amount + $payout_amount;
                    $wallet->amount =  $final_wallet_amount;
                    $wallet->save();
                    // $activity_data = [
                    //     'activity_type' => 'wallet_payout_transfer',
                    //     'transfer_amount' => $payout_amount ,
                    //     'wallet' => $wallet,
                    // ];
                    // $this->sendNotification($activity_data);


                }
            }
        }

        if ( request()->is('api*')){
          $message = __('messages.created_success',['form' => 'Provider Payout']);
          return comman_message_response($message);
        }

     if($payout_status=='queued'){

        // Redirect back to the same create page with success message
        return redirect()->route('providerpayout.create', ['id' => $provider_id])->with('success', __('messages.queue_message',['form' => 'Provider Payout']));

       }
    if ($request->redirect_type == 'collect_money') {
        // Redirect back to the same create page with success message
        return redirect()->route('providerpayout.create', ['id' => $provider_id])->with('success', __('messages.created_success', ['form' => 'Provider Payout']));
    }


        // Redirect back to the same create page with success message
        return redirect()->route('providerpayout.create', ['id' => $provider_id])->with('success', __('messages.created_success',['form' => 'Provider Payout']));

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($id != auth()->user()->id && !auth()->user()->hasRole(['admin', 'demo_admin'])) {
            return redirect(route('home'))->withErrors(trans('messages.demo_permission_denied'));
        }
        $providerdata = User::where('user_type', 'provider')->where('id', $id)->first();
        if (! $providerdata) {
            return redirect()
                ->route(auth()->user()->hasAnyRole(['admin', 'demo_admin']) ? 'earning' : 'home')
                ->withErrors(__('messages.no_providers_found'));
        }
        //
        $pageTitle = __('messages.list_form_title',['form' => __('messages.providerpayout_list')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('providerpayout.view', compact('pageTitle','auth_user','assets','id','providerdata'));
    }


    public function ProviderPayout_index_data(DataTables $datatable,$id)
    {
        $query = ProviderPayout::where('provider_id',$id);

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable ->eloquent($query)
        ->editColumn('payment_method', function($payout) {
            return !empty($payout->payment_method) ? $payout->payment_method : 'cash';
        })
        ->addColumn('bank_name', function($payout) {

        if($payout->payment_method == 'bank'){
            $bank = Bank::where('id',$payout->bank_id)->value('bank_name');
            return $bank;
        }
        else{
            return '-';
        }

        })
        ->editColumn('provider_id', function($payout) {
            return ($payout->providers != null && isset($payout->providers)) ? $payout->providers->display_name : '-';
        })
        ->editColumn('amount', function($payout) {
            return ($payout->amount != null && isset($payout->amount)) ? getPriceFormat($payout->amount) : '-';
        })
        ->editColumn('created_at', function($payout) {
            return $payout->created_at;
        })
        ->addIndexColumn()
        ->rawColumns(['bank_name'])
        ->toJson();
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
        $providerpayout = ProviderPayout::find($id);
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.providerpayout_list')] );

        if($providerpayout != '') {
            $providerpayout->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.providerpayout_list')] );
        }
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

}

