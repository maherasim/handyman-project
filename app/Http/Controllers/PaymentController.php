<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PaymentHistory;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\PaymentPostJOb;
use App\Models\PaymentPostJObHistory;

use App\Models\PostJobBid;
use App\Models\ProviderPayout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Facade\Ignition\DumpRecorder\Dump;
use Yajra\DataTables\DataTables;
use App\Models\Booking;
use App\Models\CommissionEarning;
use Carbon\Carbon;

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
        $pageTitle = __('messages.payments');
        $assets = ['datatable'];

        return view('payment.index', compact('pageTitle', 'assets', 'filter'));
    }

    public function cashIndex($id)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.cash_history')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('paymenthistory.index', compact('pageTitle', 'assets', 'auth_user', 'id'));
    }
    public function postjobcashApprove(Request $request)
{
    //dd($request->all());
    $id = $request->input('rowIds'); // this will be a single string/int
//dd( $id );
    if (empty($id)) {
        return redirect()->back()->with('error', __('messages.no_records_selected'));
    }

    // Update payment
    $payment = PaymentPostJOb::findOrFail($id);
    //dd($payment);
    $payment->payment_status = 'Verified';
    $payment->status = $request->status;
    
    $data=$payment->save();
 
    // Update CommissionEarning
    CommissionEarning::where('payment_id', $id)
        ->update(['commission_status' => 'paid']);

    // Update ProviderPayout
    ProviderPayout::where('payment_id', $id)
        ->update(['status' => 'paid']);

        return response()->json(['status' => true, 'message' => __('Cash Approved Successfully')]);

}

    
    public function paymentjobrequest()
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.job_requests')]);
        $assets = ['datatable'];
        return view('paymentjobrequest.index', compact('pageTitle', 'assets'));
    }
    public function postjobcashIndex()
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.cash_history')]);
        $assets = ['datatable'];
        return view('paymentjobrequest.cash', compact('pageTitle', 'assets'));
    }
    public function postjobwalletIndex()
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.wallet_history')]);
        $assets = ['datatable'];
        return view('paymentjobrequest.wallet', compact('pageTitle', 'assets'));
    }
    public function postjobwallet_index_data(DataTables $datatable, Request $request)
    {
        $query = PaymentPostJOb::query()
            ->myPayment()
            ->where('payment_type', 'wallet');
    
        // Apply status condition ONLY for admins
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->where('status', 0);
        }
    
        if (!$request->order) {
            $query->orderBy('created_at', 'DESC');
        }
    
        $filter = $request->filter;
    
        if (!empty($filter) && !empty($filter['column_status'])) {
            $query->where('payment_status', $filter['column_status']);
        }
    
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  
                            id="datatable-row-' . $row->id . '"  
                            name="datatable_ids[]" 
                            value="' . $row->id . '" 
                            onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->orderColumn('id', function ($query, $order) {
                $query->leftJoin('post_job_bids', 'post_job_bids.id', '=', 'payment_post_jobs.post_job_bid_request_id')
                    ->leftJoin('post_job_requests', 'post_job_requests.id', '=', 'post_job_bids.post_request_id')
                    ->orderBy('payment_post_jobs.id', $order)
                    ->orderBy('post_job_requests.title', $order);
            })
            ->editColumn('customer_id', function ($payment) {
                return view('payment.user', compact('payment'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('payment_post_jobs.*')
                    ->join('users as customers', 'customers.id', '=', 'payment_post_jobs.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("{$datetime->date_format} {$datetime->time_format}", strtotime($query->datetime));
            })
            ->addColumn('action', function ($payment) {
                return view('paymentjobrequest.action', compact('payment'))->render();
            })
            ->editColumn('history', function ($query) {
                return '<a href="' . route('paymentjobrequest.history', $query->id) . '" 
                            class="btn btn-primary btn-sm">'
                            . __('messages.view') . '</a>';
            })
            ->editColumn('payment_status', function ($query) {
                if ($query->payment_status !== null) {
                    return '<span class="text-center text-white badge bg-primary">'
                            . str_replace('_', " ", ucfirst($query->payment_status)) .
                            '</span>';
                }
                return '<span class="text-center d-block">-</span>';
            })
            ->editColumn('total_amount', function ($query) {
                return getPriceFormat($query->total_amount);
            })
            ->addColumn('post_job', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'check', 'payment_status', 'id', 'history'])
            ->toJson();
    }

    public function postjobcash_index_data(DataTables $datatable, Request $request)
    {
        $query = PaymentPostJOb::query()
            ->myPayment()
            ->where('payment_type', 'bank_transfer');
    
        // Apply status condition ONLY for admins
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->where('status', 0);
        }
    
        if (!$request->order) {
            $query->orderBy('created_at', 'DESC');
        }
    
        $filter = $request->filter;
    
        if (!empty($filter) && !empty($filter['column_status'])) {
            $query->where('payment_status', $filter['column_status']);
        }
    
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  
                            id="datatable-row-' . $row->id . '"  
                            name="datatable_ids[]" 
                            value="' . $row->id . '" 
                            onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->orderColumn('id', function ($query, $order) {
                $query->leftJoin('post_job_bids', 'post_job_bids.id', '=', 'payment_post_jobs.post_job_bid_request_id')
                    ->leftJoin('post_job_requests', 'post_job_requests.id', '=', 'post_job_bids.post_request_id')
                    ->orderBy('payment_post_jobs.id', $order)
                    ->orderBy('post_job_requests.title', $order);
            })
            ->editColumn('customer_id', function ($payment) {
                return view('payment.user', compact('payment'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('payment_post_jobs.*')
                    ->join('users as customers', 'customers.id', '=', 'payment_post_jobs.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("{$datetime->date_format} {$datetime->time_format}", strtotime($query->datetime));
            })
            ->addColumn('action', function ($payment) {
                return view('paymentjobrequest.action', compact('payment'))->render();
            })
            ->editColumn('history', function ($query) {
                return '<a href="' . route('paymentjobrequest.history', $query->id) . '" 
                            class="btn btn-primary btn-sm">'
                            . __('messages.view') . '</a>';
            })
            ->editColumn('payment_status', function ($query) {
                if ($query->payment_status !== null) {
                    return '<span class="text-center text-white badge bg-primary">'
                            . str_replace('_', " ", ucfirst($query->payment_status)) .
                            '</span>';
                }
                return '<span class="text-center d-block">-</span>';
            })
            ->editColumn('total_amount', function ($query) {
                return getPriceFormat($query->total_amount);
            })
            ->addColumn('post_job', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'check', 'payment_status', 'id', 'history'])
            ->toJson();
    }
    
    public function paymentjobrequest_index_data(DataTables $datatable, Request $request)
    {
        $query = PaymentPostJOb::query()->myPayment()
        ->where(function ($q) {
            $q->where('payment_type', '!=', 'bank_transfer')
              ->where('payment_type', '!=', 'wallet') // Exclude wallet
              ->orWhere(function ($sub) {
                  $sub->where('payment_type', 'bank_transfer')->where('status', 1);
              });
        });
    

        if (!$request->order) {
            $query->orderBy('created_at', 'DESC');
        }
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
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($query) {
                $postTitle = optional($query->postJobRequest)->title;
                return $postTitle ? e($postTitle) : '-';
            })
            ->orderColumn('id', function ($query, $order) {
                $query->leftJoin('post_job_bids', 'post_job_bids.id', '=', 'payment_post_jobs.post_job_bid_request_id')
                    ->leftJoin('post_job_requests', 'post_job_requests.id', '=', 'post_job_bids.post_request_id')
                    ->orderBy('payment_post_jobs.id', $order)
                    ->orderBy('post_job_requests.title', $order);
            })


            ->editColumn('customer_id', function ($payment) {
                return view('payment.user', compact('payment'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('payment_post_jobs.*')
                    ->join('users as customers', 'customers.id', '=', 'payment_post_jobs.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('datetime', function ($query) {
                return $query->created_at ? $query->created_at->format('Y-m-d H:i') : '-';
            })
            
            ->editColumn('history', function ($query) {
                return '<a href="' . route('paymentjobrequest.history', $query->id) . '" class="btn btn-primary btn-sm">' . __('messages.view') . '</a>';
            })
            ->editColumn('payment_status', function ($query) {
                $payment = $query->payment_status;
                if ($payment !== null) {
                    $payment_status = '<span class="text-center text-white badge bg-primary">' . str_replace('_', " ", ucfirst($payment)) . '</span>';
                } else {
                    $payment_status = '<span class="text-center d-block">-</span>';
                }
                return $payment_status;
            })


            ->editColumn('total_amount', function ($query) {
                return getPriceFormat($query->total_amount);
            })
            ->addColumn('post_job', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            // ->addColumn('action', function ($payment) {
            //     return view('payment.action', compact('payment'))->render();
            // })
            ->addIndexColumn()
            ->rawColumns(['action', 'check', 'payment_status', 'id', 'history'])


            ->toJson();
    }

    public function paymentjobrequest_history($id)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.job_requests')]);
        $assets = ['datatable'];
        return view('paymentjobrequest.history', compact('pageTitle', 'assets', 'id'));
    }


    public function paymentjobrequest_history_data(DataTables $datatable, $id)
    {
        $query = PaymentPostJobHistory::where('payment_id', $id);


        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery();
        }

        return $datatable->eloquent($query)
            ->editColumn('sender_id', function ($payment) {
                return $payment->sender ? $payment->sender->display_name : '-';
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
            ->editColumn('text', function ($query) {
                return $query->text;
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
            })
            ->addColumn('created_at', function ($payment) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("$datetime->date_format $datetime->time_format", strtotime($payment->created_at));
            })
            ->addIndexColumn()
            ->toJson();
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
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
            })
            ->addColumn('created_at', function ($payment) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("$datetime->date_format $datetime->time_format", strtotime($payment->created_at));
            })
            ->addIndexColumn()
            ->toJson();
    }


    public function cashDatatable(Request $request)
    {
        $filter = [
            'payment_status' => $request->payment_status,
        ];
        $pageTitle = __('messages.cash_payments');
        $assets = ['datatable'];
        return view('payment.cash', compact('pageTitle', 'assets', 'filter'));
    }



    public function payAdvance(Request $request, $id)
    {
        //    dd($id);
        dd($request->all());
        $user = auth()->user();
        $post = PostJobBid::findOrFail($id);

        // Allow client-provided amount; otherwise calculate from bid price
        $requestedAmount = $request->input('amount');
        if (is_string($requestedAmount) && trim($requestedAmount) === '') {
            $requestedAmount = null;
        }
        $advanceAmount = is_null($requestedAmount)
            ? (($post->price * $post->advance_percent) / 100)
            : (float) $requestedAmount;
        if ($advanceAmount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid advance amount'], 422);
        }
        // Check wallet balance
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet || $wallet->amount < $advanceAmount) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance'], 400);
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $wallet->amount -= $advanceAmount;
            $wallet->save();

            // Update post status
            $post->status = 'advance_paid';
            $post->save();

            // Compute commission and payouts
            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = 10;
            if (is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)) {
                $adminCommissionPercent = (float) $adminCommissionSetting->value;
            }

            $adminCommissionAmount = ($advanceAmount * $adminCommissionPercent) / 100.0;
            $providerPayoutAmount = max(0, $advanceAmount - $adminCommissionAmount);

            // Credit admin wallet with commission
            $adminUserId = User::where('user_type', 'admin')->value('id');
            if ($adminUserId) {
                Wallet::firstOrCreate(['user_id' => $adminUserId])->increment('amount', $adminCommissionAmount);
            }

            // Credit provider wallet with remaining payout
            if ($providerPayoutAmount > 0) {
                Wallet::firstOrCreate(['user_id' => $post->provider_id])->increment('amount', $providerPayoutAmount);

                // Create provider payout record
                ProviderPayout::create([
                    'provider_id'    => $post->provider_id,
                    'amount'         => $providerPayoutAmount,
                    'payment_method' => 'wallet',
                    'paid_date'      => Carbon::now(),
                    'status'         => 'paid',
                    'description'    => "Advance payout for Bid #{$post->id}",
                ]);
            }

            // Link to booking if exists for post_request_id
            $booking = null;
            if (!empty($post->post_request_id)) {
                $booking = Booking::where('post_request_id', $post->post_request_id)->latest('id')->first();
            }

            // Commission earnings records (only if booking exists as booking_id is required)
            if ($booking) {
                if ($adminUserId && $adminCommissionAmount > 0) {
                    CommissionEarning::create([
                        'booking_id'         => $booking->id,
                        'user_type'          => 'admin',
                        'employee_id'        => $adminUserId,
                        'commission_amount'  => $adminCommissionAmount,
                        'commission_status'  => 'paid',
                    ]);
                }

                if ($providerPayoutAmount > 0) {
                    CommissionEarning::create([
                        'booking_id'         => $booking->id,
                        'user_type'          => 'provider',
                        'employee_id'        => $post->provider_id,
                        'commission_amount'  => $providerPayoutAmount,
                        'commission_status'  => 'paid',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Advance payment of €{$advanceAmount} successful",
                'balance' => $wallet->amount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Payment failed'], 500);
        }
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
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($payment) {
                if ($payment->booking) {
                    return '<a class="btn-link btn-link-hover" href="' . route('booking.show', $payment->booking->id) . '"> #' . $payment->booking->id . '</a>';
                }
                return '-';
            })
            ->editColumn('booking_id', function ($payment) {
                return $payment->booking->service->name ?? '-';
            })
            ->filterColumn('booking_id', function ($query, $keyword) {
                $query->whereHas('booking.service', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('customer_id', function ($payment) {
                return view('payment.user', compact('payment'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                return date("$datetime->date_format / $datetime->time_format", strtotime($query->datetime));
            })
            ->editColumn('total_amount', function ($payment) {
                return getPriceFormat($payment->total_amount);
            })
            ->editColumn('history', function ($payment) {
                return '<a class="btn-link btn-link-hover" href="' . route('cash.index', $payment->id) . '">View</a>';
            })
            ->editColumn('status', function ($query) {
                return $query->payment_status
                    ? '<span class="text-center badge badge-primary1">' . str_replace('_', ' ', ucfirst($query->payment_status)) . '</span>'
                    : '<span class="text-center d-block">-</span>';
            })
            ->editColumn('action', function ($payment) {
                if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
                    return set_admin_approved_cash($payment->id) . ' ' . view('payment.cashaction', compact('payment'))->render();
                }
                return '';
            })
            ->addIndexColumn()
            ->rawColumns(['check', 'history', 'action', 'id', 'status'])
            ->toJson();
    }




    public function index_data(DataTables $datatable, Request $request)
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
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($query) {
                $booking = optional($query->booking);
                if ($booking && $booking->id) {
                    return "<a class='btn-link btn-link-hover' href=" . route('booking.show', $booking->id) . ">#" . $booking->id . "</a>";
                }
                $postTitle = optional($query->postJobRequest)->title;
                return $postTitle ? e($postTitle) : '-';
            })

            ->orderColumn('id', function ($query, $order) {
                $query->leftJoin('post_job_requests', 'post_job_requests.id', '=', 'payments.post_job_request_id')
                    ->orderBy('payments.booking_id', $order)
                    ->orderBy('post_job_requests.title', $order);
            })
            ->editColumn('booking_id', function ($query) {
                if (!empty($query->booking->bookingPackage)) {
                    $service_name = optional(optional($query->booking)->bookingPackage)->name . " (" . __('messages.service_package') . ")";
                } else {
                    $service_name = optional(optional($query->booking)->service)->name . " (" . __('messages.service') . ")";
                }
                if ($query->booking && $query->booking->service) {
                    return $service_name;
                }
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->filterColumn('booking_id', function ($query, $keyword) {
                $query->where(function ($sub) use ($keyword) {
                    $sub->whereHas('booking.service', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    })
                        ->orWhereHas('postJobRequest', function ($q) use ($keyword) {
                            $q->where('title', 'like', '%' . $keyword + '%');
                        });
                });
            })
            ->orderColumn('booking_id', function ($query, $order) {
                $query->leftJoin('bookings', 'bookings.id', '=', 'payments.booking_id')
                    ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
                    ->leftJoin('post_job_requests', 'post_job_requests.id', '=', 'payments.post_job_request_id')
                    ->orderBy('services.name', $order)
                    ->orderBy('post_job_requests.title', $order);
            })
            ->editColumn('customer_id', function ($payment) {
                return view('payment.user', compact('payment'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('payments.*')
                    ->join('users as customers', 'customers.id', '=', 'payments.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                $date = date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
                return $date;
            })
            ->editColumn('payment_status', function ($query) {
                $payment = $query->payment_status;
                if ($payment !== null) {
                    $payment_status = '<span class="text-center text-white badge bg-primary">' . str_replace('_', " ", ucfirst($payment)) . '</span>';
                } else {
                    $payment_status = '<span class="text-center d-block">-</span>';
                }
                return $payment_status;
            })


            ->editColumn('total_amount', function ($query) {
                return getPriceFormat($query->total_amount);
            })
            ->addColumn('post_job', function ($query) {
                return optional($query->postJobRequest)->title ?: '-';
            })
            ->addColumn('action', function ($payment) {
                return view('payment.action', compact('payment'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'check', 'payment_status', 'id'])
            ->toJson();
    }




    public function index_data_handyman(DataTables $datatable, Request $request)
    {
        $query = Payment::query()
            ->myPayment()
            ->with(['handymanEarning' => function ($q) {
                $q->where('user_type', 'handyman');
            }])
            ->where(function ($q) {
                $q->where('payment_type', '!=', 'bank_transfer')
                    ->orWhere(function ($sub) {
                        $sub->where('payment_type', 'bank_transfer')->where('status', 1);
                    });
            })
            ->groupBy('booking_id'); // <- Important for uniqueness


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
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('id', function ($query) {
                $booking = optional($query->booking);
                return $booking->id
                    ? "<a class='btn-link btn-link-hover' href=" . route('booking.show', $booking->id) . ">#" . $booking->id . "</a>"
                    : '-';
            })

            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('payments.booking_id', $order);
            })


            ->editColumn('booking_id', function ($query) {
                if (!empty($query->booking->bookingPackage)) {
                    $service_name = optional(optional($query->booking)->bookingPackage)->name . " (" . __('messages.service_package') . ")";
                } else {
                    $service_name = optional(optional($query->booking)->service)->name . " (" . __('messages.service') . ")";
                }

                return ($query->customer_id != null && isset($query->booking->service)) ? $service_name : '-';
            })
            ->filterColumn('booking_id', function ($query, $keyword) {
                $query->whereHas('booking.service', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
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
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('payments.*')
                    ->join('users as customers', 'customers.id', '=', 'payments.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('datetime', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                $date = date("$datetime->date_format $datetime->time_format", strtotime($query->datetime));
                return $date;
            })
            ->editColumn('payment_status', function ($query) {
                $payment = $query->payment_status;
                if ($payment !== null) {
                    $payment_status = '<span class="text-center text-white badge bg-primary">' . str_replace('_', " ", ucfirst($payment)) . '</span>';
                } else {
                    $payment_status = '<span class="text-center d-block">-</span>';
                }
                return $payment_status;
            })
            ->addColumn('handyman_earning', function ($payment) {
                // Optional: eager load in controller for performance
                $earning = optional($payment->handymanEarning)->commission_amount;
                // dd(  $earning );
                return getPriceFormat($earning ?? 0);
            })


            ->editColumn('total_amount', function ($query) {
                return getPriceFormat($query->total_amount);
            })
            ->addColumn('action', function ($payment) {
                return view('payment.action', compact('payment'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'check', 'payment_status', 'id'])
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
                // 1. Update Payment table
                Payment::whereIn('id', $ids)->update([
                    'status' => $request->status,
                    'payment_status' => 'Verified',
                ]);

                // 2. Update CommissionEarning table
                $data = CommissionEarning::whereIn('payment_id', $ids)
                    ->update(['commission_status' => 'paid']);

                // 3. Update ProviderPayout table
                ProviderPayout::whereIn('payment_id', $ids)
                    ->update(['status' => 'paid']);

                $message = 'Bulk Payment Status Updated';
                break;

            case 'delete':
                Payment::whereIn('id', $ids)->delete();

                // Optionally, delete related CommissionEarning and ProviderPayout entries
                CommissionEarning::whereIn('payment_id', $ids)->delete();
                ProviderPayout::whereIn('payment_id', $ids)->delete();

                $message = 'Bulk Payment Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
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
        if (demoUserPermission()) {
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $document = Payment::find($id);
        $msg = __('messages.msg_fail_to_delete', ['name' => __('messages.payment')]);

        if ($document != '') {

            $document->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.payment')]);
        }
        if (request()->is('api/*')) {
            return comman_message_response($msg);
        }
        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function cashApprove($id)
    {
      //  dd($id);
        // Approve a cash payment reported by provider; admin is verifying here
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $admin = json_decode($sitesetup->value);
        $paymentdata = Payment::where('id', $id)->first();
        $parent_payment_history = PaymentHistory::where('status', 'pending_by_admin')
            ->where('payment_id', $id)->first();
//dd($parent_payment_history);
        // Update the existing payment history record instead of creating new one
        if ($parent_payment_history) {
            $parent_payment_history->status = config('constant.PAYMENT_HISTORY_STATUS.APPROVED_ADMIN');
            $parent_payment_history->text = __('messages.cash_approved', [
                'amount' => getPriceFormat((float)$paymentdata->total_amount), 
                'name' => get_user_name(admin_id())
            ]);
            $parent_payment_history->save();
        }

        $booking = Booking::where('id', $paymentdata->booking_id)->first();

        // Determine payment type (advance or remaining)
        $paymentType = $parent_payment_history->type ?? $paymentdata->type ?? null; // 'advance_payment' or 'remaining'

        // Commission percentage
        $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;
        $admin_user_id = \App\Models\User::where('user_type', 'admin')->value('id');

        if ($paymentType === 'advance_payment') {
            // Advance payment: Don't touch booking status, update payment status to advanced_paid
            $paymentdata->payment_status = 'advanced_paid';
            $paymentdata->status = '1';
            $paymentdata->save();

            $booking->advance_paid_amount = $paymentdata->total_amount;
            $booking->save();

            // Create/mark admin commission as paid on advance (credit admin wallet)
            $admin_commission_amount = ($paymentdata->total_amount * $admin_commission_percentage) / 100;

            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid',
            ]);
        } elseif ($paymentType === 'full_payment') {
            // Remaining payment: Update payment status to completed
            $paymentdata->payment_status = 'completed';
            $paymentdata->status = '1';
            $paymentdata->save();

            // Complete booking
            $booking->status = 'complete';
            $booking->save();

            // Mark all related commission earnings as paid
            CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
        } else {
            // Default case: Treat as full payment (backward compatibility)
            $paymentdata->payment_status = 'paid';
            $paymentdata->status = '1';
            $paymentdata->save();

            // Complete booking
            $booking->status = 'accept';
            $booking->save();

            // Mark all related commission earnings as paid
            CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
        }

        $msg = __('messages.approve_successfully');
        return redirect()->back()->withSuccess($msg);
    }
}
