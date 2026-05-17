<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Service;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use App\Models\WalletHistory;
use App\Models\Payment;
use App\Models\Bank;
use App\Models\PaymentPostJObHistory;
use App\Models\PaymentPostJOb;
use App\Models\Country;
use App\Models\PaymentHistory;
use App\Models\SubCategory;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Setting;
use DB;
use Carbon\Carbon;
use App\Models\ProviderPayout;
use App\Models\CommissionEarning;
use App\Models\PostJobBid;
use App\Models\PostJobBidRating;
use App\Models\PostJobBidCustomerRating;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use App\Mail\PostJobBankTransferPaymentNotificationMail;
use App\Models\PostJobExtraCharge;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Log;

class PostJobRequestController extends Controller
{
    use NotificationTrait;

    private function formatStatusBadge(?string $status): string
    {
        $status = (string) ($status ?? '');
        $label = $status;
        $class = 'badge bg-secondary text-dark';

        switch (strtolower($status)) {
            case 'requested':
                $label = 'Requested';
                $class = 'badge bg-primary-subtle text-dark';
                break;
            case 'accepted':
                $label = 'Accepted';
                $class = 'badge bg-success text-dark';
                break;
            case 'in_progress':
                $label = 'IN progress';
                $class = 'badge bg-primary text-dark';
                break;
            case 'in_process':
                $label = 'IN process';
                $class = 'badge bg-info text-dark';
                break;
            case 'advance_payment':
                $label = 'Advance Payment';
                $class = 'badge bg-warning text-dark';
                break;
            case 'advance_paid':
                $label = 'Advance Paid';
                $class = 'badge bg-success text-dark';
                break;
            case 'advance_payment_pending_approval':
            case 'advance_payment_pending':
                $label = 'Advance (Pending Approval)';
                $class = 'badge bg-warning text-dark';
                break;
            case 'remaining_payment_pending_approval':
            case 'remaining_payment_pending':
                $label = 'Remaining (Pending Approval)';
                $class = 'badge bg-warning text-dark';
                break;
            case 'assigned':
                $label = 'Assigned';
                $class = 'badge bg-info text-dark';
                break;
            case 'hold':
            case 'on_hold':
                $label = 'On Hold';
                $class = 'badge bg-warning text-dark';
                break;
            case 'done':
                $label = 'Done';
                $class = 'badge bg-success text-dark';
                break;
            case 'remaining_paid':
                $label = 'Remaining Paid';
                $class = 'badge bg-success text-dark';
                break;
            case 'confirm_done':
                $label = 'Completed';
                $class = 'badge bg-success text-dark';
                break;
            case 'completed':
                $label = 'Completed';
                $class = 'badge bg-success text-dark';
                break;
            case 'cancelled':
                $label = 'Cancelled';
                $class = 'badge bg-danger text-dark';
                break;
            default:
                $label = ucfirst(str_replace('_', ' ', $status));
                $class = 'badge bg-secondary text-dark';
        }

        return '<span class="' . $class . '">' . e($label) . '</span>';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = trans('messages.job_request_list');
        $auth_user = authSession();
        $assets = ['datatable'];

        return view('postrequest.index', compact('pageTitle', 'auth_user', 'assets', 'filter'));
    }
    public function showbid($postRequestId)
    {
        // Prefer the bid whose status matches the PostJobRequest status
        $post = PostJobRequest::findOrFail($postRequestId);
        $desiredStatus = (string) ($post->status ?? '');

        $bidQuery = PostJobBid::with([
            'provider:id,display_name',
            'customer:id,display_name',
            'postrequest',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'postrequest.postBidList:id,post_request_id',
            'extraCharges',
            'ratings' => fn ($q) => $q->publicVisible()->with(['provider', 'customer']),
            'customerRatings' => fn ($q) => $q->publicVisible()->with(['customer', 'provider']),
        ])->where('post_request_id', $postRequestId);

        // Try to get bid matching the post's current status
        $bid = null;
        if ($desiredStatus !== '') {
            $bid = (clone $bidQuery)->where('status', $desiredStatus)->first();
        }

        // Fallback: any non-cancelled bid
        if (!$bid) {
            $bid = (clone $bidQuery)->where('status', '!=', 'cancelled')->first();
        }

        // If still none, redirect to the bids list page with a friendly message
        if (!$bid) {
            return redirect()
                ->route('post-job-request.bids', ['id' => $postRequestId])
                ->with('info', 'No proposals yet for this job. Please check back later.');
        }

        // Rate buttons: customer rates provider → post_job_bid_ratings; provider rates customer → post_job_bid_customer_ratings
        $customerHasRatedProvider = PostJobBidRating::where('post_job_bid_id', $bid->id)->exists();
        $canCustomerRate = strtolower((string)($bid->status ?? '')) === 'remaining_paid';
        $showRateNowButton = $canCustomerRate && !$customerHasRatedProvider;

        $providerHasRatedCustomer = PostJobBidCustomerRating::where('post_job_bid_id', $bid->id)
            ->where('provider_id', $bid->provider_id)
            ->exists();
        $canProviderRate = in_array(strtolower((string)($bid->status ?? '')), ['remaining_paid', 'completed']);
        $showRateCustomerButton = $canProviderRate && !$providerHasRatedCustomer;

        return view('postrequest.show', compact('bid', 'showRateCustomerButton', 'showRateNowButton', 'customerHasRatedProvider'));
    }

    /**
     * Display a single bid details page by Bid ID.
     */
    public function showBidById($bidId)
    {
        $bid = PostJobBid::with([
            'provider:id,display_name',
            'customer:id,display_name',
            'postrequest:id,title,customer_id,status,provider_id,remaining_percent,type,start_date,end_date,total_budget,city_id,country_id,job_price,street_address,house_number,working_address,total_hours,price_type,total_days',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'postrequest.postBidList:id,post_request_id',
            'extraCharges',
            'ratings' => fn ($q) => $q->publicVisible()->with(['provider', 'customer']),
            'customerRatings' => fn ($q) => $q->publicVisible()->with(['customer', 'provider']),
        ])->findOrFail($bidId);

        $providerHasRatedCustomer = PostJobBidCustomerRating::where('post_job_bid_id', $bid->id)
            ->where('provider_id', $bid->provider_id)
            ->exists();
        $canProviderRate = in_array(strtolower((string)($bid->status ?? '')), ['remaining_paid', 'completed']);
        $showRateCustomerButton = $canProviderRate && !$providerHasRatedCustomer;

        $customerHasRatedProvider = PostJobBidRating::where('post_job_bid_id', $bid->id)->exists();
        $canCustomerRate = strtolower((string)($bid->status ?? '')) === 'remaining_paid';
        $showRateNowButton = $canCustomerRate && !$customerHasRatedProvider;

        return view('postrequest.show', compact('bid', 'showRateCustomerButton', 'showRateNowButton', 'customerHasRatedProvider'));
    }


    public function bidshowindex()
    {

        $auth_user = authSession();


        $assignedPost = PostJobBid::where('provider_id', $auth_user->id)
            ->where('status', 'accepted')
            ->first();
        //dd($auth_user->id);
        $advance_payment = PostJobBid::where('customer_id', $auth_user->id)
            ->where('status', 'advance_payment')
            ->first();
        //dd($advance_payment);
        // If the viewer is a customer, get their latest assigned/in_progress job


        $postJobBids = PostJobBid::where('provider_id', $auth_user->id)->get();

        $pageTitle = trans('messages.list_form_title', ['form' => trans('messages.postbid')]);
        $assets = ['datatable'];

        return view('postrequest.mybid', compact(
            'pageTitle',
            'auth_user',
            'assets',
            'postJobBids',
            'assignedPost',
            'advance_payment'
        ));
    }

    public function setAdvanceSplit(Request $request, $id)
    {
        $request->validate([
            'advance_percent' => 'required|integer|min:20|max:99',
            'remaining_percent' => 'required|integer|min:1|max:80',
        ], [
            'advance_percent.min' => 'Advance must be between 20% and 99%.',
            'advance_percent.max' => 'Advance must be between 20% and 99%.',
        ]);

        $post = PostJobRequest::findOrFail($id);
        $post->advance_percent = $request->advance_percent;
        $post->remaining_percent = $request->remaining_percent;
        $post->status = 'in_progress';
        $post->save();

        // Notify customer: Employer set payment split
        try {
            $bid = PostJobBid::with(['postrequest', 'provider', 'customer'])->find($post->accepted_bid_id);
            if ($bid) {
                $this->sendNotification([
                    'activity_type'    => 'post_job_bid_status_update',
                    'post_job'         => $bid,
                    'bid_status'       => 'Payment split set',
                    'notify_recipient' => 'user',
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('setAdvanceSplit notification failed: ' . $e->getMessage());
        }

        return response()->json(['status' => true, 'message' => 'Payment split set & work started.']);
    }
    public function bidshow(Request $request)
    {
        $auth_user = authSession();
 
        $query = PostJobBid::query()->with([
            'provider:id,display_name',
            'customer:id,display_name',
            // Include required PostJobRequest fields for card details
            'postrequest:id,title,customer_id,status,provider_id,remaining_percent,type,start_date,end_date,total_budget,city_id,country_id,accepted_bid_id',
            // Eager-load location and bids for counts
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'postrequest.postBidList:id,post_request_id',
        ]);

        if ($auth_user->user_type === 'provider') {
            // If viewing a specific post, show all bids for that post (don't restrict to own bid)
            if (!$request->filled('post_request_id')) {
                $query->where('provider_id', $auth_user->id);
            }
        } elseif ($auth_user->user_type === 'user') {
            // Users see all their posts (don't filter by status here)
            $query->whereHas('postrequest', fn($q) => $q->where('customer_id', $auth_user->id));
        }

        if ($request->filled('post_request_id')) {
            $query->where('post_request_id', (int) $request->post_request_id);
        }

        $postJobBids = $query->get();

        return DataTables::of($postJobBids)
            ->addIndexColumn()
            ->addColumn('id', fn($bid) => $bid->id)
            ->addColumn('provider_name', fn($bid) => $bid->provider->display_name ?? 'N/A')
            ->addColumn('customer_name', fn($bid) => $bid->customer->display_name ?? 'N/A')
            ->addColumn('post_title', fn($bid) => $bid->postrequest->title ?? 'N/A')
            // Extra fields for cards
            ->addColumn('city', function ($bid) {
                $post = $bid->postrequest;
                return ($post && $post->city) ? ($post->city->name ?? 'N/A') : 'N/A';
            })
            ->addColumn('country', function ($bid) {
                $post = $bid->postrequest;
                return ($post && $post->country) ? ($post->country->name ?? 'N/A') : 'N/A';
            })
            ->addColumn('job_type', function ($bid) {
                return $bid->postrequest->type ?? null;
            })
            ->addColumn('start_date', function ($bid) {
                $d = optional($bid->postrequest)->start_date;
                return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : null;
            })
            ->addColumn('end_date', function ($bid) {
                $d = optional($bid->postrequest)->end_date;
                return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : null;
            })
            ->addColumn('total_budget', function ($bid) {
                $post = $bid->postrequest;
                if (!$post) {
                    return '-';
                }
                $amount = getPriceFormat((float) ($post->total_budget ?? 0));
                $type = strtolower((string)($post->price_type ?? $post->job_price ?? 'fixed'));
                $label = $type === 'hourly' ? 'hourly' : ($type === 'daily' ? 'daily' : ($type === 'fixed' ? 'fixed' : $type));
                return trim($amount . ' ' . $label);
            })
            ->addColumn('applications', function ($bid) {
                $post = $bid->postrequest;
                if (!$post) {
                    return 0;
                }
                // Use eager-loaded collection to avoid N+1
                return isset($post->postBidList) ? $post->postBidList->count() : $post->postBidList()->count();
            })
            ->addColumn('created_at', function ($bid) {
                return $bid->created_at ? $bid->created_at->format('Y-m-d') : null;
            })
            ->editColumn('price', function ($bid) {
                $amount = getPriceFormat((float) ($bid->price ?? 0));
                $post = $bid->postrequest;
                $type = $post ? strtolower((string)($post->price_type ?? $post->job_price ?? 'fixed')) : 'fixed';
                $label = $type === 'hourly' ? 'hourly' : ($type === 'daily' ? 'daily' : ($type === 'fixed' ? 'fixed' : $type));
                return trim($amount . ' ' . $label);
            })
            ->addColumn('status', fn($bid) => $this->formatStatusBadge($bid->status ?? ''))
            ->addColumn('hold_reason', fn($bid) => $bid->hold_reason ?? null)
            ->addColumn('has_advance_paid', function ($bid) {
                // Determine if an advance payment (customer side) was completed for this bid
                // Payments are stored with post_job_request_id referencing the PostJobBid id
                $payments = Payment::where('post_job_request_id', $bid->id)
                    ->where('payment_status', 'remaining_paid')
                    ->get();

                foreach ($payments as $payment) {
                    $details = json_decode($payment->other_transaction_detail, true);
                    if (is_array($details) && isset($details['type']) && $details['type'] === 'advance') {
                        return true;
                    }
                }
                return false;
            })
            ->addColumn('action', function ($bid) use ($auth_user) {
                $post = $bid->postrequest;

                if (!$post) return '-';

                // Provider: Start Work if assigned
                // if ($auth_user->user_type === 'provider' && $post->status === 'assigned' && $post->provider_id == $bid->provider_id) {
                //     return '<button class="btn btn-sm btn-primary startWorkBtn" data-post-id="'.$post->id.'">Start Work</button>';
                // }

                // Customer: Accept bid if requested
                if ($auth_user->user_type === 'user' && $post->status === 'requested' && $bid->status !== 'accepted') {
                    return '<button class="btn btn-sm btn-success acceptBid" data-id="' . $bid->id . '">Accept</button>';
                }

                // Provider: show Start Work when advance is paid on bid
                if ($auth_user->user_type === 'provider' && (int)$auth_user->id === (int)$bid->provider_id && $bid->status === 'advance_paid') {
                    return '<button class="btn btn-sm btn-primary updateStatusBtn" data-id="' . $bid->id . '" data-status="in_progress">Start Work</button>';
                }

                // Customer: show Let's Start Work when already in progress (idempotent)
                if ($auth_user->user_type === 'user' && (int)$auth_user->id === (int)$bid->customer_id && in_array($bid->status, ['in_progress', 'in_process'])) {
                    return '<button class="btn btn-sm btn-info updateStatusBtn" data-id="' . $bid->id . '" data-status="in_process">Let\'s Start Work</button>';
                }

                // Provider: when in progress or in process, show Hold and Done
                if ($auth_user->user_type === 'provider' && (int)$auth_user->id === (int)$bid->provider_id && in_array($bid->status, ['in_progress', 'in_process', 'hold'])) {
                    $buttons = [];
                    $buttons[] = '<button class="btn btn-sm btn-warning holdBidBtn" data-id="' . $bid->id . '">Hold</button>';
                    $buttons[] = '<button class="btn btn-sm btn-success updateStatusBtn" data-id="' . $bid->id . '" data-status="done">Done</button>';
                    return implode(' ', $buttons);
                }

                // Otherwise
                return $bid->status === 'accepted'
                    ? '<span class="badge badge-success">Accepted</span>'
                    : '-';
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }
    public function payAdvance(Request $request, $id)
    {
       // dd($request->all());
        $user = auth()->user();
        $post = PostJobBid::findOrFail($id);
    
        // Get payment type and amount from frontend
        $paymentType = strtolower((string)$request->input('type', 'advance'));
        $requestedAmount = (float)$request->input('amount');
    
        // Validate payment type
        if (!in_array($paymentType, ['advance', 'remaining'])) {
            return response()->json(['status' => false, 'message' => 'Invalid payment type'], 422);
        }
    
        // Validate amount
        if ($requestedAmount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid amount'], 422);
        }
    
        // Use frontend amount directly
        $payAmount = $requestedAmount;
    
        // Prepare variables based on payment type
        $formattedAmount = getPriceFormat($payAmount);
        if ($paymentType === 'remaining') {
           // dd($payAmount,'remaing');
            $txnPrefix = 'REM-';
            $customerActivity = "Remaining payment of {$formattedAmount} for Bid #{$post->id} has been paid";

            $providerActivity = "Remaining payment of {$formattedAmount} for Bid #{$post->id} has been paid";
            $paymentMetaType = 'remaining';
            $payoutStatus = 'remaining paid';
            $newPostStatus = 'remaining_paid';
            $successMsg = "Remaining payment of {$formattedAmount} successful";
        } else {
           // dd($payAmount,'advance');
            $txnPrefix = 'ADV-';
            $customerActivity = "Advance payment of {$formattedAmount} for Bid #{$post->id} has been paid";

            $providerActivity = "Advance payment of {$formattedAmount} for Bid #{$post->id} has been paid";
            $paymentMetaType = 'advance';
            $payoutStatus = 'advance paid';
            $newPostStatus = 'advance_paid';
            $successMsg = "Advance payment of {$formattedAmount} successful";
        }
    
        // Check wallet balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->amount < $payAmount) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance'], 400);
        }
    
        DB::beginTransaction();
        try {
            // Create one transaction ID for both debit & credit
            $txnId = uniqid($txnPrefix);
    
            /*
            |--------------------------------------------------------------------------
            | Debit from Customer Wallet
            |--------------------------------------------------------------------------
            */
            $wallet->decrement('amount', $payAmount);
    
          //dd($customerActivity,'customerActivity');
           // Wallet history (customer) - Debit entry for sender
           WalletHistory::create([
                'datetime'         => now(),
                'user_id'          => $user->id, // Sender (customer)
                'activity_type'    => 'debit',
                'activity_message' => $customerActivity,
                'activity_data'    => json_encode([
                    'user_id' => $post->provider_id, // Receiver ID
                    'credit_debit_amount' => $payAmount,
                    'amount' => $wallet->amount, // Balance after transaction
                    'transaction_type' => 'Debit',
                ]),
            ]);
   // dd($data);
            // Payment entry (customer)
            $payment = PaymentPostJOb::create([
                'customer_id'               => $user->id,
                'datetime'                  => now(),
                'post_job_bid_request_id'   => $post->id,
                'discount'                  => 0,
                'total_amount'              => $payAmount,
                'payment_type'              => 'wallet',
                'txn_id'                    => $txnId,
                'payment_status'            => 'completed',
                'other_transaction_detail'  => json_encode([
                    'type'     => $paymentMetaType,
                    'bid_id'   => $post->id,
                    'provider' => $post->provider_id,
                ]),
            ]);
    
            // Update post status
            $post->status = $newPostStatus;
            $post->save();
    
            /*
            |--------------------------------------------------------------------------
            | Commission + Provider Payout
            |--------------------------------------------------------------------------
            */
            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
                ? (float) $adminCommissionSetting->value
                : 10;
    
            $adminCommissionAmount = ($payAmount * $adminCommissionPercent) / 100.0;
            $providerPayoutAmount  = max(0, $payAmount - $adminCommissionAmount);
    
            if ($providerPayoutAmount > 0) {
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $post->provider_id],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerPayoutAmount);
                $providerWallet->refresh();

                // Wallet history (provider) - Credit entry for receiver
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $post->provider_id, // Receiver (provider)
                    'activity_type'    => 'credit',
                    'activity_message' => $providerActivity,
                    'activity_data'    => json_encode([
                        'user_id' => $user->id, // Sender ID (customer)
                        'credit_debit_amount' => $providerPayoutAmount,
                        'amount' => $providerWallet->amount, // Balance after transaction
                        'transaction_type' => 'Credit',
                    ]),
                ]);
    
                // Payment history (provider)
                PaymentPostJObHistory::create([
                    'datetime'          => now(),
                    'payment_id'        => $payment->id,
                    'receiver_id'       => $post->provider_id,
                    'sender_id'         => $user->id,
                    'action'            => 'credit',
                    'text'              => $providerActivity,
                    'post_job_request_id' => $post->id,
                    'activity_data'     => json_encode([
                        'amount'  => $payAmount,
                        'balance' => $providerWallet->amount,
                    ]),
                    'status'            => 'completed',
                ]);
    
                // Save provider payout record
                ProviderPayout::create([
                    'provider_id'    => $post->provider_id,
                    'amount'         => $providerPayoutAmount,
                    'payment_method' => 'wallet',
                    'paid_date'      => now(),
                    'status'         => $payoutStatus,
                    'description'    => ucfirst($paymentMetaType) . " payout for Bid #{$post->id}",
                ]);
            }
    
            /*
            |--------------------------------------------------------------------------
            | Commission Records
            |--------------------------------------------------------------------------
            */
            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'user_type'         => 'admin',
                    'post_job_bid_request_id'         => $post->id,
                    'employee_id'       => 1,
                    'commission_amount' => $adminCommissionAmount,
                    'commission_status' => 'paid',
                ]);
            }
    
            if ($providerPayoutAmount > 0) {
                CommissionEarning::create([
                    'user_type'         => 'provider',
                    'post_job_bid_request_id'         => $post->id,
                    'employee_id'       => $post->provider_id,
                    'commission_amount' => $providerPayoutAmount,
                    'commission_status' => 'paid',
                ]);
            }
    
            DB::commit();

            // Notify provider: customer paid advance or remaining (email + notification)
            $post->load(['provider', 'customer', 'postrequest']);
            $this->sendNotification([
                'activity_type'     => 'post_job_bid_status_update',
                'post_job'          => $post,
                'notify_recipient'  => 'provider',
                'bid_status'        => $paymentType === 'remaining' ? 'Remaining paid' : 'Advance paid',
            ]);

            return response()->json([
                'status'  => true,
                'message' => $successMsg,
                'balance' => $wallet->amount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Payment failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Unified status update API for PostJobBid.
     * Allows provider to move advance_paid -> in_progress, and
     * allows user to confirm in_progress idempotently.
     */
    public function updateBidStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'hold_reason' => 'nullable|string|max:500'
        ]);
    
        $bid = PostJobBid::findOrFail($id);
    
        // Assuming post_request_id exists in PostJobBid
        $postjob = PostJobRequest::findOrFail($bid->post_request_id);
    
        // Update bid status
        $bid->status = $request->input('status');
    
        // If hold → save reason
        if ($bid->status === 'hold' && $request->filled('hold_reason')) {
            $bid->hold_reason = $request->input('hold_reason');
        }
        $bid->save();
    
        // Update postjob status + accepted_bid_id logic
        if ($bid->status === 'cancelled') {
            $postjob->status = 'requested'; // special case
            $postjob->cancel_bid_id = $bid->id; //
            $postjob->accepted_bid_id = null; // reset if cancelled
        } elseif ($bid->status === 'accepted') {
            $postjob->status = 'accepted';
            $postjob->accepted_bid_id = $bid->id; // ✅ store accepted bid id
        } else {
            $postjob->status = $bid->status;
        }
    
        $postjob->save();

        // Notify the OTHER party: provider updated → notify customer; customer updated → notify provider
        try {
            $bid->load(['postrequest', 'provider', 'customer']);
            $isProviderUpdating = (int) auth()->id() === (int) $bid->provider_id;
            $this->sendNotification([
                'activity_type'    => 'post_job_bid_status_update',
                'post_job'         => $bid,
                'bid_status'       => $bid->status,
                'notify_recipient' => $isProviderUpdating ? 'user' : 'provider',
            ]);
        } catch (\Throwable $e) {
            \Log::warning('post_job_bid_status_update notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
        ]);
    }
    




    /**
     * Add extra charges to a bid: price += amount * quantity
     */
    public function addExtraCharges(Request $request, $id)
    {
        // Support aggregated multi-item payload
        if ($request->has('items') && is_array($request->input('items'))) {
            $request->validate([
                'items'            => 'required|array|min:1',
                'items.*.title'    => 'required|string|max:255',
                'items.*.amount'   => 'required|numeric|min:0.01',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
    
            $items = $request->input('items');
            $totalExtra = 0.0;
            $totalQty   = 0;
            $bid = PostJobBid::findOrFail($id);
            // reset existing lines to avoid duplication
            $bid->extraCharges()->delete();

            foreach ($items as $line) {
                $lineAmount = (float) ($line['amount'] ?? 0);
                $lineQty    = (int) ($line['quantity'] ?? 0);
                $totalExtra += $lineAmount * $lineQty;
                $totalQty   += $lineQty;

                PostJobExtraCharge::create([
                    'post_job_bid_id' => $bid->id,
                    'title'           => (string) $line['title'],
                    'amount'          => $lineAmount,
                    'quantity'        => $lineQty,
                ]);
            }
    
            $bid->extra_charges = $totalExtra; // store summed charges
            $bid->quantity      = $totalQty;   // store summed qty for reference
            $bid->status        = 'completed';
            $bid->save();

            // Notify customer: Employer added extra charges and marked job as completed
            try {
                $bid->load(['postrequest', 'provider', 'customer']);
                $this->sendNotification([
                    'activity_type'    => 'post_job_bid_status_update',
                    'post_job'         => $bid,
                    'bid_status'       => 'completed',
                    'notify_recipient' => 'user',
                ]);
            } catch (\Throwable $e) {
                \Log::warning('addExtraCharges notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'status'        => true,
                'message'       => 'Extra charges added successfully & bid marked as completed',
                'extra_charges' => $bid->extra_charges,
                'quantity'      => $bid->quantity,
                'new_status'    => $bid->status,
            ]);
        }

        // Fallback: single-row compatibility
        $request->validate([
            'title'    => 'required|string|max:255',
            'amount'   => 'required|numeric|min:0.01',
            'quantity' => 'nullable|integer|min:1'
        ]);
    
        $bid = PostJobBid::findOrFail($id);
        $quantity    = (int)($request->input('quantity') ?? 1);
        $extraAmount = (float)$request->input('amount');
    
        // Replace any existing lines with this single item
        $bid->extraCharges()->delete();
        PostJobExtraCharge::create([
            'post_job_bid_id' => $bid->id,
            'title'           => (string) $request->input('title'),
            'amount'          => $extraAmount,
            'quantity'        => $quantity,
        ]);

        $bid->extra_charges = $extraAmount;
        $bid->quantity      = $quantity;
        $bid->status        = 'completed';
        $bid->save();

        // Notify customer: Employer added extra charges and marked job as completed
        try {
            $bid->load(['postrequest', 'provider', 'customer']);
            $this->sendNotification([
                'activity_type'    => 'post_job_bid_status_update',
                'post_job'         => $bid,
                'bid_status'       => 'completed',
                'notify_recipient' => 'user',
            ]);
        } catch (\Throwable $e) {
            \Log::warning('addExtraCharges notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Extra charges added successfully & bid marked as completed',
            'extra_charges' => $bid->extra_charges,
            'quantity'      => $bid->quantity,
            'new_status'    => $bid->status,
        ]);
    }

    public function createPostJobStripePayment(Request $request, $id)
    {
        $bid = PostJobBid::findOrFail($id);
        $user = auth()->user();
    
        if ((int)$user->id !== (int)$bid->customer_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }
    
        // Validate payment type & amount
        $type = strtolower((string)$request->input('type', 'advance')); // advance | remaining
        $payAmount = (float)$request->input('amount', 0);
    
        // Compute payable server-side if not provided or invalid
        if ($payAmount <= 0) {
            $quantity      = (int) ($bid->quantity ?? 1);
            $quantity      = $quantity > 0 ? $quantity : 1;
            $baseAmount    = (float) ($bid->price ?? 0) * $quantity;
            $extrasTotal   = (float) $bid->extraCharges()->get()->reduce(function($carry, $charge) {
                $q = (int) ($charge->quantity ?? 1);
                return $carry + ((float) $charge->amount * ($q > 0 ? $q : 1));
            }, 0.0);
            $totalDue      = $baseAmount + $extrasTotal;
            $advancePct    = (float) ($bid->advance_percent ?? 0);
            $remainingPct  = (float) ($bid->remaining_percent ?? 0);
    
            if ($type === 'advance' && $advancePct > 0) {
                $payAmount = round(($totalDue * $advancePct) / 100.0, 2);
            } elseif ($type === 'remaining' && $remainingPct > 0) {
                $payAmount = round(($totalDue * $remainingPct) / 100.0, 2);
            } else {
                $payAmount = round($totalDue, 2);
            }
        }
    
        $metaType = $type;
    
        // Get Stripe key
        $payment_geteway_value = getPaymentMethodkey('stripe');
        $stripe_secret = $payment_geteway_value['stripe_key'] ?? null;
    
        if (!$stripe_secret) {
            return response()->json(['status' => false, 'message' => 'Stripe not configured'], 500);
        }
    
        $stripe = new \Stripe\StripeClient($stripe_secret);
    
        // Resolve currency dynamically from settings
        try {
            $sitesetupdata = Setting::getValueByKey('site-setup', 'site-setup');
            $countryId = is_object($sitesetupdata) ? ($sitesetupdata->default_currency ?? null) : ($sitesetupdata['default_currency'] ?? null);
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string) ($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
        $currencyCode = 'EUR';
        }
    
        // Support two flows: Checkout (web) and PaymentIntent (PaymentSheet for mobile)
        if ($request->boolean('use_checkout')) {
            $baseURL = config('app.url') ?: 'https://frobster.com';
            try {
                $session = $stripe->checkout->sessions->create([
                    'success_url' => $baseURL . '/postjob/save-stripe-payment/' . $bid->id .
                        '?type=' . $metaType . '&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $baseURL . '/postjob/bid/' . $bid->id,
                    'payment_method_types' => ['card'],
                    'billing_address_collection' => 'required',
                    'line_items' => [[
                        'price_data' => [
                            'currency' => $currencyCode,
                            'product_data' => [
                                'name' => 'Post Job Bid #' . $bid->id . ' - ' . ucfirst($metaType) . ' Payment',
                            ],
                            'unit_amount' => stripe_unit_amount_from_decimal($payAmount, $currencyCode),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'metadata' => [
                        'bid_id' => (string) $bid->id,
                        'type' => $metaType,
                        'customer_id' => (string) $bid->customer_id,
                        'provider_id' => (string) $bid->provider_id,
                    ],
                ]);
        
                return response()->json([
                    'status' => true,
                    'checkout_session_id' => $session->id,
                    'url' => $session->url
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
        }
    
        // Default: PaymentIntent for mobile PaymentSheet
        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => stripe_unit_amount_from_decimal($payAmount, $currencyCode),
                'currency' => $currencyCode,
                'description' => 'Post Job Bid #' . $bid->id . ' - ' . ucfirst($metaType) . ' Payment',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'bid_id' => (string) $bid->id,
                    'type' => $metaType,
                    'customer_id' => (string) $bid->customer_id,
                    'provider_id' => (string) $bid->provider_id,
                ],
            ]);
    
            return response()->json([
                'status' => true,
                'payment_intent_id' => $intent->id,
                'client_secret' => $intent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    

    public function savePostJobStripePayment(Request $request, $id)
    {
       // dd($request->all());
        $type      = strtolower((string)$request->query('type', 'advance')); // advance | remaining
        $sessionId = $request->query('session_id'); // ✅ comes from Stripe success_url

        if (!$sessionId) {
            return redirect()->route('post-job-bid.show', ['id' => $id])
                ->withErrors('Missing Stripe session ID.');
        }

        $bid = PostJobBid::findOrFail($id);
        $adminUser  = User::where('user_type', 'admin')->first();
        $providerId = $bid->provider_id;
        // 🔹 Verify session with Stripe
        try {
            $session = getstripePaymnetId($sessionId, 'stripe');
        } catch (\Exception $e) {
            return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
                ->withErrors('Stripe verification failed: ' . $e->getMessage());
        }

        if (!empty($session['payment_intent']) && ($session['payment_status'] ?? '') === 'paid') {

            $txnId     = $session['payment_intent'];
            $payAmount = (float)($session['amount_total'] / 100); // Stripe returns amount in cents

            // 🔹 Commission setup
            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
                ? (float) $adminCommissionSetting->value
                : 10;

            $adminCommissionAmount = ($payAmount * $adminCommissionPercent) / 100.0;
            $providerEarningAmount = $payAmount - $adminCommissionAmount;
            
            $finalPayment = PaymentPostJOb::create([
                'customer_id' => $bid->customer_id,
                'provider_id' => $bid->provider_id,
                'post_job_bid_request_id' => $bid->id,
                'total_amount' => $payAmount,
                'discount' => 0,
                'payment_type' => 'stripe',
                'payment_status' => 'completed',
                'status' => $type, // advance or remaining
                'txn_id' => $txnId,
                'other_transaction_detail' => json_encode([
                    'session_id' => $sessionId,
                    'admin_commission' => $adminCommissionAmount,
                    'provider_earning' => $providerEarningAmount,
                ]),
            ]);

            // 🔹 Commission earnings
            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'admin',
                    'employee_id'         => $adminUser?->id ?? 1,
                    'commission_amount'   => $adminCommissionAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            if ($providerEarningAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'provider',
                    'employee_id'         => $providerId,
                    'commission_amount'   => $providerEarningAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            // 🔹 Provider payout
            if ($providerEarningAmount > 0) {
                ProviderPayout::create([
                    'provider_id'         => $providerId,
                    'amount'              => $providerEarningAmount,
                    'payment_method'      => 'Stripe',
                    'paid_date'           => now(),
                    'status'              => 'paid',
                    'booking_id'          => null,
                    'post_job_request_id' => $bid->id,
                    'payment_gateway'     => 'Stripe',
                ]);

                // Credit provider wallet
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $providerId],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerEarningAmount);
                $providerWallet->refresh();
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $providerId,
                    'activity_type'    => 'credit',
                    'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . " payment for Bid #{$bid->id} received (Stripe)",
                    'activity_data'    => json_encode([
                        'credit_debit_amount' => $providerEarningAmount,
                        'amount'              => $providerWallet->amount,
                        'transaction_type'    => 'Credit',
                    ]),
                ]);
            }

            // 🔹 Payment history
            PaymentPostJObHistory::create([
                'payment_id'  => $finalPayment->id,
                'parent_id'   => null,
                'action'      => 'customer_send_provider',
                'status'      => 'completed',
                'sender_id'   => $finalPayment->customer_id,
                'receiver_id' => $providerId,
                'datetime'    => now(),
                'total_amount' => round((float) $request->amount, 2), // ✅ ONLY amount paid
                'txn_id'      => $finalPayment->txn_id,
                'type'        => 'stripe',
                'text'        => __('messages.payment_transfer', [
                    'from'   => get_user_name($finalPayment->customer_id),
                    'to'     => get_user_name($providerId),
                    'amount' => number_format((float) $finalPayment->total_amount, 2), // ✅ Just for display
                ]),
                'other_transaction_detail' => null, // Optional: or just skip commission info
            ]);
            
            // 🔹 Update PostJobBid status
            $bid->status = ($type === 'advance') ? 'advance_paid' : 'remaining_paid';
            $bid->save();

            // Notify provider: customer paid advance or remaining (email + notification)
            $bid->load(['provider', 'customer', 'postrequest']);
            $this->sendNotification([
                'activity_type'     => 'post_job_bid_status_update',
                'post_job'          => $bid,
                'notify_recipient'  => 'provider',
                'bid_status'        => $type === 'advance' ? 'Advance paid' : 'Remaining paid',
            ]);

            return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
                ->with('success', 'Stripe payment processed successfully. Commission and payouts recorded.');
        }

        return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
            ->withErrors('Stripe payment not completed.');
    }

    /**
     * JSON endpoint to confirm a PaymentIntent (PaymentSheet/mobile flow)
     * Expects: payment_intent_id, optional type (advance|remaining)
     */
    public function confirmPostJobStripePaymentIntent(Request $request, $id)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'nullable|string',
            'type' => 'nullable|in:advance,remaining'
        ]);
        $type = strtolower((string) $request->input('type', 'advance'));
        $intentId = (string) $request->input('payment_intent_id');
        $paymentMethodId = (string) $request->input('payment_method_id', '');

        $bid = PostJobBid::findOrFail($id);
        $adminUser  = User::where('user_type', 'admin')->first();
        $providerId = $bid->provider_id;

        // Stripe client
        $payment_geteway_value = getPaymentMethodkey('stripe');
        $stripe_secret = $payment_geteway_value['stripe_key'] ?? null;
        if (!$stripe_secret) {
            return response()->json(['status' => false, 'message' => 'Stripe not configured'], 500);
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        try {
            $intent = $stripe->paymentIntents->retrieve($intentId, []);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe verification failed: ' . $e->getMessage(),
            ], 422);
        }
        
        // If not yet confirmed and a payment_method_id is supplied, confirm server-side
        if (in_array(($intent->status ?? ''), ['requires_confirmation', 'requires_payment_method', 'requires_action']) && !empty($paymentMethodId)) {
            try {
                $intent = $stripe->paymentIntents->confirm($intentId, [
                    'payment_method' => $paymentMethodId,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe confirm failed: ' . $e->getMessage(),
                    'intent_status' => $intent->status ?? null,
                ], 422);
            }
        }

        if (($intent->status ?? '') === 'succeeded') {
            $txnId = $intent->id;
            $payAmount = (float) (($intent->amount_received ?? $intent->amount ?? 0) / 100.0);

            // Commission setup
            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
                ? (float) $adminCommissionSetting->value
                : 10;
            $adminCommissionAmount = ($payAmount * $adminCommissionPercent) / 100.0;
            $providerEarningAmount = $payAmount - $adminCommissionAmount;

            $finalPayment = PaymentPostJOb::create([
                'customer_id' => $bid->customer_id,
                'provider_id' => $bid->provider_id,
                'post_job_bid_request_id' => $bid->id,
                'total_amount' => $payAmount,
                'discount' => 0,
                'payment_type' => 'stripe',
                'payment_status' => 'completed',
                'status' => $type,
                'txn_id' => $txnId,
                'other_transaction_detail' => json_encode([
                    'payment_intent_id' => $intentId,
                    'admin_commission' => $adminCommissionAmount,
                    'provider_earning' => $providerEarningAmount,
                ]),
            ]);

            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'admin',
                    'employee_id'         => $adminUser?->id ?? 1,
                    'commission_amount'   => $adminCommissionAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }
            if ($providerEarningAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'provider',
                    'employee_id'         => $providerId,
                    'commission_amount'   => $providerEarningAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }
            ProviderPayout::create([
                'provider_id'         => $providerId,
                'amount'              => $providerEarningAmount,
                'payment_method'      => 'Stripe',
                'paid_date'           => now(),
                'status'              => 'paid',
                'booking_id'          => null,
                'post_job_request_id' => $bid->id,
                'payment_gateway'     => 'Stripe',
            ]);

            // Credit provider wallet
            if ($providerEarningAmount > 0) {
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $providerId],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerEarningAmount);
                $providerWallet->refresh();
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $providerId,
                    'activity_type'    => 'credit',
                    'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . " payment for Bid #{$bid->id} received (Stripe)",
                    'activity_data'    => json_encode([
                        'credit_debit_amount' => $providerEarningAmount,
                        'amount'              => $providerWallet->amount,
                        'transaction_type'    => 'Credit',
                    ]),
                ]);
            }

            PaymentPostJObHistory::create([
                'payment_id'  => $finalPayment->id,
                'parent_id'   => null,
                'action'      => 'customer_send_provider',
                'status'      => 'completed',
                'sender_id'   => $finalPayment->customer_id,
                'receiver_id' => $providerId,
                'datetime'    => now(),
                'total_amount' => $finalPayment->total_amount,
                'txn_id'      => $finalPayment->txn_id,
                'type'        => 'stripe',
                'text'        => __('messages.payment_transfer', [
                    'from'   => get_user_name($finalPayment->customer_id),
                    'to'     => get_user_name($providerId),
                    'amount' => number_format((float) $finalPayment->total_amount, 2),
                ]),
                'other_transaction_detail' => null,
            ]);
            $bid->status = ($type === 'advance') ? 'advance_paid' : 'remaining_paid';
            $bid->save();

            // Notify provider: customer paid advance or remaining (email + notification)
            $bid->load(['provider', 'customer', 'postrequest']);
            $this->sendNotification([
                'activity_type'     => 'post_job_bid_status_update',
                'post_job'          => $bid,
                'notify_recipient'  => 'provider',
                'bid_status'        => $type === 'advance' ? 'Advance paid' : 'Remaining paid',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stripe payment processed successfully. Commission and payouts recorded.',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Stripe payment not completed.',
        ], 422);
    }
    /**
     * JSON endpoint for apps: confirm Stripe Checkout and update bid status
     */
    public function confirmPostJobStripePayment(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'required|string',
            'type'       => 'nullable|in:advance,remaining'
        ]);

        $type      = strtolower((string)$request->input('type', 'advance'));
        $sessionId = (string) $request->input('session_id');

        $bid = PostJobBid::findOrFail($id);
        $adminUser  = User::where('user_type', 'admin')->first();
        $providerId = $bid->provider_id;

        try {
            $session = getstripePaymnetId($sessionId, 'stripe');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe verification failed: ' . $e->getMessage(),
            ], 422);
        }

        if (!empty($session['payment_intent']) && ($session['payment_status'] ?? '') === 'paid') {
            $txnId     = $session['payment_intent'];
            $payAmount = (float)($session['amount_total'] / 100);

            $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
            $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
                ? (float) $adminCommissionSetting->value
                : 10;

            $adminCommissionAmount = ($payAmount * $adminCommissionPercent) / 100.0;
            $providerEarningAmount = $payAmount - $adminCommissionAmount;

            $finalPayment = PaymentPostJOb::create([
                'customer_id' => $bid->customer_id,
                'provider_id' => $bid->provider_id,
                'post_job_bid_request_id' => $bid->id,
                'total_amount' => $payAmount,
                'discount' => 0,
                'payment_type' => 'stripe',
                'payment_status' => 'completed',
                'status' => $type,
                'txn_id' => $txnId,
                'other_transaction_detail' => json_encode([
                    'session_id' => $sessionId,
                    'admin_commission' => $adminCommissionAmount,
                    'provider_earning' => $providerEarningAmount,
                ]),
            ]);

            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'admin',
                    'employee_id'         => $adminUser?->id ?? 1,
                    'commission_amount'   => $adminCommissionAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            if ($providerEarningAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'           => 'provider',
                    'employee_id'         => $providerId,
                    'commission_amount'   => $providerEarningAmount,
                    'commission_status'   => 'paid',
                    'payment_id'          => $finalPayment->id,
                ]);
            }

            if ($providerEarningAmount > 0) {
                ProviderPayout::create([
                    'provider_id'         => $providerId,
                    'amount'              => $providerEarningAmount,
                    'payment_method'      => 'Stripe',
                    'paid_date'           => now(),
                    'status'              => 'paid',
                    'booking_id'          => null,
                    'post_job_request_id' => $bid->id,
                    'payment_gateway'     => 'Stripe',
                ]);

                // Credit provider wallet
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $providerId],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerEarningAmount);
                $providerWallet->refresh();
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $providerId,
                    'activity_type'    => 'credit',
                    'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . " payment for Bid #{$bid->id} received (Stripe)",
                    'activity_data'    => json_encode([
                        'credit_debit_amount' => $providerEarningAmount,
                        'amount'              => $providerWallet->amount,
                        'transaction_type'    => 'Credit',
                    ]),
                ]);
            }

            PaymentPostJObHistory::create([
                'payment_id'  => $finalPayment->id,
                'parent_id'   => null,
                'action'      => 'customer_send_provider',
                'status'      => 'completed',
                'sender_id'   => $finalPayment->customer_id,
                'receiver_id' => $providerId,
                'datetime'    => now(),
                'total_amount' => $payAmount,
                'txn_id'      => $finalPayment->txn_id,
                'type'        => 'stripe',
                'text'        => __('messages.payment_transfer', [
                    'from'   => get_user_name($finalPayment->customer_id),
                    'to'     => get_user_name($providerId),
                    'amount' => number_format((float) $finalPayment->total_amount, 2),
                ]),
                'other_transaction_detail' => null,
            ]);

            $bid->status = ($type === 'advance') ? 'advance_paid' : 'remaining_paid';
            $bid->save();

            // Notify provider: customer paid advance or remaining (email + notification)
            $bid->load(['provider', 'customer', 'postrequest']);
            $this->sendNotification([
                'activity_type'     => 'post_job_bid_status_update',
                'post_job'          => $bid,
                'notify_recipient'  => 'provider',
                'bid_status'        => $type === 'advance' ? 'Advance paid' : 'Remaining paid',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stripe payment processed successfully',
                'new_status' => $bid->status,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Stripe payment not completed',
        ], 400);
    }




    public function setAdvance(Request $request, $id)
    {
        $post = PostJobRequest::findOrFail($id);

        $request->validate([
            'advance_percent' => 'required|numeric|min:1|max:100',
        ]);

        $advancePercent = $request->advance_percent;
        $advanceAmount = ($post->price * $advancePercent) / 100;
        $remainingAmount = $post->price - $advanceAmount;

        $payment = Payment::updateOrCreate(
            [
                'post_job_request_id' => $post->id,
                'payment_type'        => 'advance'
            ],
            [
                'customer_id'     => $post->customer_id,
                'total_amount'    => $advanceAmount,
                'discount'        => 0,
                'payment_status'  => 'pending', // waiting for payment
                'datetime'        => now(),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Advance terms saved successfully. Awaiting customer payment.'
        ]);
    }
    public function createPostJobPayPalPayment(Request $request, $id)
    {
        try {
            $bid = PostJobBid::findOrFail($id);
            
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json(['status' => false, 'error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            // Check user authorization
            if ((int)$user->id !== (int)$bid->customer_id) {
                return response()->json(['status' => false, 'error' => 'Unauthorized access to this bid'], 403);
            }
    
            // Get payment type and amount directly from frontend
            $type = strtolower((string)$request->input('type', 'advance')); // 'advance' or 'remaining'
            $payAmount = (float)$request->input('amount'); // exact value from frontend
    
            if ($payAmount <= 0) {
                return response()->json(['status' => false, 'error' => 'Invalid amount'], 422);
            }
    
            // Get PayPal credentials from settings
            $paymentGatewayValue = getPaymentMethodkey('paypal');
    
            $clientId = $paymentGatewayValue['paypal_client_id'] ?? null;
            $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
            $mode = $paymentGatewayValue['mode'] ?? 'sandbox';
    
            if (!$clientId || !$clientSecret) {
                return response()->json(['status' => false, 'error' => 'PayPal not configured'], 500);
            }
    
            // Setup PayPal environment
            $environment = $mode === 'live'
                ? new ProductionEnvironment($clientId, $clientSecret)
                : new SandboxEnvironment($clientId, $clientSecret);
    
            $client = new PayPalHttpClient($environment);
    
            // Base URL – use API return/cancel URLs when request is from API (mobile/app)
            $baseURL = config('app.url') ?: 'https://frobster.com';
            $isApi = $request->expectsJson() || $request->is('api/*') || str_starts_with((string) $request->path(), 'api/');
            if ($isApi) {
                $baseURL = rtrim($baseURL, '/') . '/api';
            }
    
            // Resolve currency dynamically from settings
            try {
                $sitesetupdata = Setting::getValueByKey('site-setup', 'site-setup');
                $countryId = is_object($sitesetupdata) ? ($sitesetupdata->default_currency ?? null) : ($sitesetupdata['default_currency'] ?? null);
                $country = $countryId ? \App\Models\Country::find($countryId) : null;
                $currencyCode = strtoupper((string) ($country->currency_code ?? 'EUR'));
            } catch (\Throwable $e) {
                $currencyCode = 'EUR';
            }
    
            // Create PayPal order
            $order = new OrdersCreateRequest();
            $order->prefer('return=representation');
            $order->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $currencyCode,
                        'value' => number_format($payAmount, 2, '.', '')
                    ],
                    'description' => 'Payment for Post Job Bid #' . $bid->id . ' (' . $type . ')'
                ]],
                'application_context' => [
                    'cancel_url' => $isApi
                        ? ($baseURL . '/postjob/paypal/cancel')
                        : ($baseURL . '/postjob/bid/' . $bid->id),
                    'return_url' => $isApi
                        ? ($baseURL . '/postjob/paypal/success/' . $bid->id . '?type=' . $type)
                        : ($baseURL . '/postjob/paypal-success/' . $bid->id . '?type=' . $type),
                    'brand_name' => env('APP_NAME'),
                    'landing_page' => 'LOGIN',
                    'user_action' => 'PAY_NOW',
                ]
            ];
    
            try {
                $response = $client->execute($order);
                $approvalLink = collect($response->result->links)->firstWhere('rel', 'approve')->href ?? null;
    
                if (!$approvalLink) {
                    return response()->json(['status' => false, 'error' => 'Unable to get PayPal approval link'], 500);
                }
    
                return response()->json(['status' => true, 'url' => $approvalLink]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false, 
                    'error' => 'PayPal Create Payment Error: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while processing your request',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function postJobPayPalSuccess(Request $request, $id)
{
    $token = $request->query('token');
    $type  = strtolower((string)$request->query('type', 'advance'));
    $bid   = PostJobBid::findOrFail($id);

    if (!$token) {
        return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
            ->with('error', 'Missing PayPal token');
    }

    // Build PayPal client using configured settings (match create step)
    $paymentGatewayValue = getPaymentMethodkey('paypal');
    $clientId = $paymentGatewayValue['paypal_client_id'] ?? null;
    $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
    $mode = $paymentGatewayValue['mode'] ?? 'sandbox';

    if (!$clientId || !$clientSecret) {
        return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
            ->with('error', 'PayPal not configured');
    }

    $environment  = $mode === 'live'
        ? new ProductionEnvironment($clientId, $clientSecret)
        : new SandboxEnvironment($clientId, $clientSecret);
    $client = new PayPalHttpClient($environment);

    $captureRequest = new OrdersCaptureRequest($token);
    $captureRequest->prefer('return=representation');

    try {
        $response = $client->execute($captureRequest);

        if (!in_array($response->statusCode, [200, 201])) {
            return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
                ->with('error', 'Payment not completed');
        }

        $txnId = $response->result->purchase_units[0]->payments->captures[0]->id ?? null;
        $payAmount = (float)($response->result->purchase_units[0]->amount->value ?? 0);

        if ($payAmount <= 0) {
            return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
                ->with('error', 'Invalid payment amount');
        }

        // 🔹 Create New Payment Record
        $payment = PaymentPostJob::create([
            'post_job_bid_request_id' => $bid->id,
            'customer_id' => $bid->customer_id,
            'datetime'                => now(),
            'payment_type'            => 'paypal',
            'payment_status'          => 'completed',
            'txn_id'                  => $txnId,
            'total_amount'            => $payAmount,
            'other_transaction_detail' => json_encode([
                'type' => $type,
                'order_id' => $token,
                'admin_commission' => 0,  // Admin commission will be calculated later
                'provider_amount' => 0,   // Provider amount will be calculated later
            ])
        ]);

        // Compute admin commission and provider payout
        $adminCommissionPercent = (float)(Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10);
        $adminCommissionAmount  = ($payAmount * $adminCommissionPercent) / 100;
        $providerAmount         = max(0, $payAmount - $adminCommissionAmount);

        // Update the payment record with the commission details
        $payment->other_transaction_detail = json_encode([
            'type' => $type,
            'order_id' => $token,
            'admin_commission' => $adminCommissionAmount,
            'provider_amount' => $providerAmount,
        ]);
        $payment->save();

        $adminUser  = User::where('user_type', 'admin')->first();
        $providerId = $bid->provider_id;

        // 🔹 Record CommissionEarning for Admin
        if ($adminCommissionAmount > 0) {
            CommissionEarning::create([
                'post_job_bid_request_id' => $bid->id,
                'user_type'               => 'admin',
                'employee_id'             => $adminUser?->id ?? 1,
                'commission_amount'       => $adminCommissionAmount,
                'commission_status'       => 'paid',
                'payment_id'              => $payment->id,
            ]);
        }

        // 🔹 Record CommissionEarning for Provider
        if ($providerAmount > 0) {
            CommissionEarning::create([
                'post_job_bid_request_id' => $bid->id,
                'user_type'               => 'provider',
                'employee_id'             => $providerId,
                'commission_amount'       => $providerAmount,
                'commission_status'       => 'paid',
                'payment_id'              => $payment->id,
            ]);

            // 🔹 Record Provider Payout
            ProviderPayout::create([
                'provider_id'         => $providerId,
                'amount'              => $providerAmount,
                'payment_method'      => 'PayPal',
                'paid_date'           => now(),
                'status'              => 'paid',
                'booking_id'          => null,
                'post_job_request_id' => $bid->id,
                'payment_gateway'     => 'PayPal',
            ]);

            // Credit provider wallet
            if ($providerAmount > 0) {
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $providerId],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerAmount);
                $providerWallet->refresh();
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $providerId,
                    'activity_type'    => 'credit',
                    'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . " payment for Bid #{$bid->id} received (PayPal)",
                    'activity_data'    => json_encode([
                        'credit_debit_amount' => $providerAmount,
                        'amount'              => $providerWallet->amount,
                        'transaction_type'    => 'Credit',
                    ]),
                ]);
            }

            // 🔹 Record PaymentHistory
            PaymentPostJObHistory::create([
                'payment_id'  => $payment->id,
                'booking_id'  => null,
                'parent_id'   => null,
                'action'      => 'customer_send_provider',
                'status'      => 'completed',
                'sender_id'   => $bid->customer_id,
                'receiver_id' => $providerId,
                'datetime'    => now(),
                'total_amount'=> $payAmount,
                'txn_id'      => $txnId,
                'type'        => 'paypal',
                'text'        => __('messages.payment_transfer', [
                    'from'   => get_user_name($bid->customer_id),
                    'to'     => get_user_name($providerId),
                    'amount' => number_format($payAmount, 2),
                ]),
                'other_transaction_detail' => json_encode([
                    'admin_commission' => $adminCommissionAmount,
                    'provider_amount'  => $providerAmount,
                ]),
            ]);
        }

        // 🔹 Update bid status
        $bid->status = ($type === 'remaining') ? 'remaining_paid' : 'advance_paid';
        $bid->save();

        // Notify provider: customer paid advance or remaining (email + notification)
        $bid->load(['provider', 'customer', 'postrequest']);
        $this->sendNotification([
            'activity_type'     => 'post_job_bid_status_update',
            'post_job'          => $bid,
            'notify_recipient'  => 'provider',
            'bid_status'        => $type === 'remaining' ? 'Remaining paid' : 'Advance paid',
        ]);

        return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
            ->with('success', 'PayPal payment completed and payout recorded.');

    } catch (\Exception $e) {
        \Log::error('PayPal capture error: ' . $e->getMessage());
        return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])
            ->with('error', 'Payment failed: ' . $e->getMessage());
    }
}

    /**
     * PayPal success callback for API only (returns JSON).
     * Used when payment is completed and user is redirected from PayPal to the API return_url.
     * URL: GET /api/postjob/paypal/success/{id}?token=...&type=advance|remaining
     */
    public function postJobPayPalSuccessApi(Request $request, $id)
    {
        $token = $request->query('token');
        $type  = strtolower((string) $request->query('type', 'advance'));
        $bid   = PostJobBid::find($id);

        if (!$bid) {
            return response()->json(['status' => false, 'message' => 'Bid not found'], 404);
        }
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Missing PayPal token'], 400);
        }

        $paymentGatewayValue = getPaymentMethodkey('paypal');
        $clientId = $paymentGatewayValue['paypal_client_id'] ?? null;
        $clientSecret = $paymentGatewayValue['paypal_secret_key'] ?? null;
        $mode = $paymentGatewayValue['mode'] ?? 'sandbox';

        if (!$clientId || !$clientSecret) {
            return response()->json(['status' => false, 'message' => 'PayPal not configured'], 500);
        }

        $environment = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);
        $client = new PayPalHttpClient($environment);

        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $client->execute($captureRequest);

            if (!in_array($response->statusCode, [200, 201])) {
                return response()->json(['status' => false, 'message' => 'Payment not completed'], 400);
            }

            $txnId = $response->result->purchase_units[0]->payments->captures[0]->id ?? null;
            $payAmount = (float) ($response->result->purchase_units[0]->amount->value ?? 0);

            if ($payAmount <= 0) {
                return response()->json(['status' => false, 'message' => 'Invalid payment amount'], 400);
            }

            $payment = PaymentPostJob::create([
                'post_job_bid_request_id' => $bid->id,
                'customer_id'             => $bid->customer_id,
                'datetime'                => now(),
                'payment_type'            => 'paypal',
                'payment_status'          => 'completed',
                'txn_id'                  => $txnId,
                'total_amount'            => $payAmount,
                'other_transaction_detail' => json_encode([
                    'type' => $type,
                    'order_id' => $token,
                    'admin_commission' => 0,
                    'provider_amount' => 0,
                ])
            ]);

            $adminCommissionPercent = (float) (Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10);
            $adminCommissionAmount  = ($payAmount * $adminCommissionPercent) / 100;
            $providerAmount         = max(0, $payAmount - $adminCommissionAmount);

            $payment->other_transaction_detail = json_encode([
                'type' => $type,
                'order_id' => $token,
                'admin_commission' => $adminCommissionAmount,
                'provider_amount' => $providerAmount,
            ]);
            $payment->save();

            $adminUser  = User::where('user_type', 'admin')->first();
            $providerId = $bid->provider_id;

            if ($adminCommissionAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'               => 'admin',
                    'employee_id'             => $adminUser?->id ?? 1,
                    'commission_amount'       => $adminCommissionAmount,
                    'commission_status'       => 'paid',
                    'payment_id'              => $payment->id,
                ]);
            }

            if ($providerAmount > 0) {
                CommissionEarning::create([
                    'post_job_bid_request_id' => $bid->id,
                    'user_type'               => 'provider',
                    'employee_id'             => $providerId,
                    'commission_amount'       => $providerAmount,
                    'commission_status'       => 'paid',
                    'payment_id'              => $payment->id,
                ]);
                ProviderPayout::create([
                    'provider_id'         => $providerId,
                    'amount'              => $providerAmount,
                    'payment_method'      => 'PayPal',
                    'paid_date'           => now(),
                    'status'              => 'paid',
                    'booking_id'          => null,
                    'post_job_request_id' => $bid->id,
                    'payment_gateway'     => 'PayPal',
                ]);
                $providerWallet = Wallet::firstOrCreate(
                    ['user_id' => $providerId],
                    ['amount' => 0]
                );
                if ($providerWallet->amount === null) {
                    $providerWallet->update(['amount' => 0]);
                }
                $providerWallet->increment('amount', $providerAmount);
                $providerWallet->refresh();
                WalletHistory::create([
                    'datetime'         => now(),
                    'user_id'          => $providerId,
                    'activity_type'    => 'credit',
                    'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . " payment for Bid #{$bid->id} received (PayPal)",
                    'activity_data'    => json_encode([
                        'credit_debit_amount' => $providerAmount,
                        'amount'              => $providerWallet->amount,
                        'transaction_type'    => 'Credit',
                    ]),
                ]);
                PaymentPostJObHistory::create([
                    'payment_id'  => $payment->id,
                    'booking_id'  => null,
                    'parent_id'   => null,
                    'action'      => 'customer_send_provider',
                    'status'      => 'completed',
                    'sender_id'   => $bid->customer_id,
                    'receiver_id' => $providerId,
                    'datetime'    => now(),
                    'total_amount'=> $payAmount,
                    'txn_id'      => $txnId,
                    'type'        => 'paypal',
                    'text'        => __('messages.payment_transfer', [
                        'from'   => get_user_name($bid->customer_id),
                        'to'     => get_user_name($providerId),
                        'amount' => number_format($payAmount, 2),
                    ]),
                    'other_transaction_detail' => json_encode([
                        'admin_commission' => $adminCommissionAmount,
                        'provider_amount'  => $providerAmount,
                    ]),
                ]);
            }

            $bid->status = ($type === 'remaining') ? 'remaining_paid' : 'advance_paid';
            $bid->save();

            $bid->load(['provider', 'customer', 'postrequest']);
            $this->sendNotification([
                'activity_type'     => 'post_job_bid_status_update',
                'post_job'          => $bid,
                'notify_recipient'  => 'provider',
                'bid_status'        => $type === 'remaining' ? 'Remaining paid' : 'Advance paid',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'PayPal payment completed and payout recorded.',
                'data'    => [
                    'bid_id'       => (int) $bid->id,
                    'post_request_id' => (int) $bid->post_request_id,
                    'payment_type' => $type,
                    'amount'       => $payAmount,
                    'txn_id'       => $txnId,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('PayPal API capture error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * PayPal cancel callback for API only (returns JSON).
     * Used when user cancels on PayPal and is redirected to the API cancel_url.
     * URL: GET /api/postjob/paypal/cancel
     */
    public function postJobPayPalCancelApi(Request $request)
    {
        return response()->json([
            'status'  => false,
            'message' => 'Payment cancelled by user.',
        ], 200);
    }

    public function getPostJobBankDetails($id)
    {
        $bid = PostJobBid::findOrFail($id);
        // Prefer provider's active bank; fallback to a simple structure
        $bank = Bank::where('provider_id', $bid->provider_id)->where('status', 1)->latest('id')->first();
        $payload = [
            'bank' => [
                'bank_name'   => $bank->bank_name ?? null,
                'holder_name' => $bank->account_holder_name ?? $bank->holder_name ?? null,
                'account_no'  => $bank->account_no ?? null,
                'iban'        => $bank->iban ?? null,
                'swift_code'  => $bank->swift_code ?? null,
                'city_code'   => $bank->city_code ?? null,
            ],
        ];
        return response()->json($payload);
    }

    public function createPostJobBankTransfer(Request $request, $id)
    {
        $bid = PostJobBid::findOrFail($id);
        $user = auth()->user();

        // Check authorization
        if ((int)$user->id !== (int)$bid->customer_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $payAmount = (float)$request->input('amount', 0);
        $type = strtolower((string)$request->input('type', 'advance')); // advance or remaining

        if ($payAmount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid amount'], 422);
        }

        // Calculate admin commission and provider earning
        $admin_commission_percentage = 10; // fixed 10%
        $admin_commission_amount = ($payAmount * $admin_commission_percentage) / 100;
        $provider_earning = $payAmount - $admin_commission_amount;

        // Create Payment record with type
        $payment = PaymentPostJOb::create([
            'customer_id' => $user->id,
        
            'post_job_bid_request_id' => $bid->id,
            'datetime' => now(),
            'discount' => 0,
            'total_amount' => number_format($payAmount, 2, '.', ''),
            'payment_type' => 'bank_transfer',
            'payment_status' => $type === 'advance' ? 'advance_pending' : 'remaining_pending',
            'status' => $type, // store whether it's advance or remaining
            'other_transaction_detail' => json_encode([
                'note' => 'User reported bank transfer; awaiting verification',
                'admin_commission' => $admin_commission_amount,
                'provider_earning' => $provider_earning,
                'payment_type' => $type,
            ]),
        ]);

        // Save commission earnings (pending)
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $provider_id = $bid->provider_id;

        CommissionEarning::create([
            'post_job_request_id' => $bid->id,
            'user_type' => 'admin',
            'employee_id' => $admin_user_id,
            'commission_amount' => $admin_commission_amount,
            'commission_status' => 'pending',
            'payment_id' => $payment->id,
        ]);

        CommissionEarning::create([
            'post_job_request_id' => $bid->id,
            'user_type' => 'provider',
            'employee_id' => $provider_id,
            'commission_amount' => $provider_earning,
            'commission_status' => 'pending',
            'payment_id' => $payment->id,
        ]);
        ProviderPayout::create([
            'provider_id' => $provider_id,
            'amount' => $provider_earning,
            'payment_method' => 'Bank Transfer',
            'paid_date' => Carbon::now(),
            'status' => 'pending', // you can set 'pending' if you want to verify first
            'booking_id' => null, // Not a normal booking
            'post_job_request_id' => $bid->id,
            'payment_gateway' => 'Bank Transfer',
        ]);
        PaymentPostJObHistory::create([
            'payment_id' => $payment->id,
           
            'parent_id' => $payment->id,
            'action' => 'customer_send_provider', // action name
            'status' => 'pending', // pending verification
            'sender_id' => $user->id,
            'receiver_id' => $provider_id,
            'datetime' => now(),
            'total_amount' => $payAmount,
            'txn_id' => null,
            'type' => 'bank_transfer',
            'text' => __('messages.payment_transfer', [
                'from' => get_user_name($user->id),
                'to' => get_user_name($provider_id),
                'amount' => getPriceFormat($payAmount),
            ]),
            'other_transaction_detail' => json_encode([
                'admin_commission' => $admin_commission_amount,
                'provider_earning' => $provider_earning,
            ]),
        ]);
        // Bank transfer: wait for admin approval before marking as paid (statuses match PaymentController postjobcashApprove)
        if ($type === 'advance') {
            $bid->status = 'advance_payment_pending';
        } elseif ($type === 'remaining') {
            $bid->status = 'remaining_payment_pending';
        }
        $bid->save();

        // Notify admin: cash/bank transfer submitted – verify at cash-payment-list
        try {
            $bid->load(['customer', 'provider', 'request']);
            Mail::to('frobminator@frobster.com')->send(new PostJobBankTransferPaymentNotificationMail($payment, $bid, $type));
        } catch (\Exception $e) {
            Log::error('Failed to send post job bank transfer notification to admin: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Waiting for admin approval.',
            'payment_id' => $payment->id,
            'admin_commission' => $admin_commission_amount,
            'provider_earning' => $provider_earning,
            'payment_type' => $type,
        ]);
    }





    // public function postJobPayPalSuccess(Request $request, $id)
    // {
    //     $token = $request->query('token');
    //     $type = strtolower((string)$request->query('type', 'advance'));
    //     $bid = PostJobBid::findOrFail($id);

    //     if (!$token) {
    //         return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])->with('error', 'Missing PayPal token');
    //     }

    //     // Build PayPal client (same as above)
    //     $clientId = 'Afyzu7BkzUMiiK6kdB0QutzIh3cSZTcCFZjko7Fl1boR_jhW034YWoyVUBnsSmu1ZbX5bdIY0HA49SY6';
    //     $clientSecret = 'EET9cnIPbSWpA6c96cqJOSI9c-feQ512YQEZXSatXCcehOJU7G9eoxnPrNoikb-lFaCt0oOTAciZ26Ka';
    //     $mode = env('PAYPAL_MODE', 'sandbox');
    //     $environment = $mode === 'live'
    //         ? new ProductionEnvironment($clientId, $clientSecret)
    //         : new SandboxEnvironment($clientId, $clientSecret);
    //     $client = new PayPalHttpClient($environment);

    //     $captureRequest = new OrdersCaptureRequest($token);
    //     $captureRequest->prefer('return=representation');

    //     try {
    //         $response = $client->execute($captureRequest);
    //         if (!in_array($response->statusCode, [200, 201])) {
    //             return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])->with('error', 'Payment not completed');
    //         }

    //         $payment = Payment::where('post_job_request_id', $bid->id)
    //             ->where('payment_type', 'paypal')
    //             ->latest('id')
    //             ->first();

    //         if (!$payment) {
    //             return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])->with('error', 'Payment not found');
    //         }

    //         // Mark payment complete and set txn id
    //         $payment->payment_status = 'completed';
    //         $payment->txn_id = $response->result->purchase_units[0]->payments->captures[0]->id ?? null;
    //         $payment->save();

    //         // Commission + provider payout (same as Stripe flow)
    //         $adminCommissionSetting = Setting::getValueByKey('admin_commission_percentage', 'site-setup');
    //         $adminCommissionPercent = is_object($adminCommissionSetting) && isset($adminCommissionSetting->value)
    //             ? (float) $adminCommissionSetting->value
    //             : 10;

    //         $payAmount = (float)$payment->total_amount;
    //         $adminCommissionAmount = ($payAmount * $adminCommissionPercent) / 100.0;
    //         $providerPayoutAmount  = max(0, $payAmount - $adminCommissionAmount);

    //         if ($providerPayoutAmount > 0) {
    //             $providerWallet = Wallet::firstOrCreate(['user_id' => $bid->provider_id]);
    //             $providerWallet->increment('amount', $providerPayoutAmount);

    //             WalletHistory::create([
    //                 'datetime'        => now(),
    //                 'user_id'         => $bid->provider_id,
    //                 'activity_type'   => 'credit',
    //                 'activity_message' => ($type === 'remaining' ? 'Remaining' : 'Advance') . ' received for Bid #' . $bid->id,
    //                 'activity_data'   => json_encode([
    //                     'amount'  => $providerPayoutAmount,
    //                     'balance' => $providerWallet->amount,
    //                 ]),
    //             ]);

    //             Payment::create([
    //                 'customer_id'             => $bid->provider_id,
    //                 'booking_id'              => null,
    //                 'datetime'                => now(),
    //                 'post_job_request_id'     => $bid->id,
    //                 'discount'                => 0,
    //                 'total_amount'            => $providerPayoutAmount,
    //                 'payment_type'            => 'wallet',
    //                 'txn_id'                  => $payment->txn_id,
    //                 'payment_status'          => 'completed',
    //                 'other_transaction_detail' => json_encode([
    //                     'type'     => $type . '_payout',
    //                     'bid_id'   => $bid->id,
    //                     'customer' => $payment->customer_id,
    //                 ]),
    //             ]);
    //         }

    //         if ($adminCommissionAmount > 0) {
    //             CommissionEarning::create([
    //                 'user_type'         => 'admin',
    //                 'employee_id'       => 1,
    //                 'commission_amount' => $adminCommissionAmount,
    //                 'commission_status' => 'paid',
    //             ]);
    //         }
    //         if ($providerPayoutAmount > 0) {
    //             CommissionEarning::create([
    //                 'user_type'         => 'provider',
    //                 'employee_id'       => $bid->provider_id,
    //                 'commission_amount' => $providerPayoutAmount,
    //                 'commission_status' => 'paid',
    //             ]);
    //         }

    //         // Update bid status
    //         $bid->status = ($type === 'remaining') ? 'remaining_paid' : 'advance_paid';
    //         $bid->save();

    //         return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])->with('success', 'Payment completed');
    //     } catch (\Exception $e) {
    //         \Log::error('PayPal capture error: ' . $e->getMessage());
    //         return redirect()->route('post-job-bid.show', ['id' => $bid->post_request_id])->with('error', 'Payment failed: ' . $e->getMessage());
    //     }
    // }

    public function acceptBid($id)
    {
        $auth_user = authSession();

        // Load the bid with relations needed for notification
        $bid = PostJobBid::with(['postrequest', 'provider', 'customer'])->findOrFail($id);

        // Ensure customer owns this job request
        if (!$bid->postrequest || (int)$auth_user->id !== (int)$bid->postrequest->customer_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Update the bid and post request
        $bid->status = 'accepted';
        $bid->save();
        $post = $bid->postrequest;
        if ($post) {
            $post->accepted_bid_id = $bid->id;
            $post->status = 'accepted';
            $post->provider_id = $bid->provider_id;
            $post->save();
        }

        // Notify provider (Employer): in-app notification + email
        try {
            $this->sendNotification([
                'activity_type' => 'user_accept_bid',
                'post_job'      => $bid,
                'job_price'     => getPriceFormat($bid->price),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('user_accept_bid notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Bid accepted successfully and status updated!'
        ]);
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $query = PostJobRequest::query();

        // If the user is not an admin, restrict to only their job requests
        if (!auth()->user()->hasAnyRole(['admin']) && auth()->user()->user_type !== 'provider') {
            $query->where('customer_id', auth()->id());
        }

        $filter = $request->filter;
        if (isset($filter) && isset($filter['column_status'])) {
            $query->where('status', $filter['column_status']);
        }

        // Count only non-cancelled applicants (bids) per job request
        $query->withCount([
            'postBidList as applicants' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }
        ]);

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('title', function ($query) {
                return $query->title;
            })
            ->addColumn('applicants', function ($query) {
                return (int) ($query->applicants ?? 0);
            })
            ->addColumn('accepted_for_current_provider', function ($row) { 
                if (auth()->user()->user_type === 'provider') {
                    return $row->postBidList()
                        ->whereHas('provider', function ($q) {
                            $q->where('id', auth()->user()->id);
                        })
                        ->where('provider_id', auth()->user()->id)
                        ->whereNotIn('status', ['requested', 'cancelled']) // ✅ Excludes both
                        ->exists();
                }
                return false;
            })
            
            ->editColumn('provider_id', function ($query) {
                return view('postrequest.provider', compact('query'));
            })
            ->editColumn('customer_id', function ($query) {
                return view('postrequest.customer', compact('query'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('post_job_requests.*')
                    ->join('users as customers', 'customers.id', '=', 'post_job_requests.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('price', function ($query) {
                $amount = getPriceFormat($query->price);
                $type = strtolower((string)($query->price_type ?? $query->job_price ?? 'fixed'));
                $label = $type === 'hourly' ? 'hourly' : ($type === 'daily' ? 'daily' : ($type === 'fixed' ? 'fixed' : $type));
                return trim($amount . ' ' . $label);
            })
            ->editColumn('start_date', function ($query) {
                return $query->start_date ? $query->start_date->format('Y-m-d') : '';
            })
            ->editColumn('end_date', function ($query) {
                return $query->end_date ? $query->end_date->format('Y-m-d') : '';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d') : '';
            })
            ->editColumn('status', function ($query) {
                return $this->formatStatusBadge($query->status);
            })
            ->addColumn('action', function ($post_job) {
                return view('postrequest.action', compact('post_job'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title', 'action', 'status', 'check'])
            ->toJson();
    }
    public function editpostjob($id)
    {
        $auth_user = authSession();
        $postJob = PostJobRequest::findOrFail($id);
    //dd( $postJob);
        // Allow admin or the owner to edit
        if (!auth()->user()->hasAnyRole(['admin']) && (int)auth()->id() !== (int)$postJob->customer_id) {
            abort(403);
        }
    
        $pageTitle = __('messages.update_form_title', ['form' => __('messages.post_job')]);
        return view('post-job-request.create', compact('postJob', 'pageTitle', 'auth_user'));
    }
   
    // ...
    protected function resolveInvoicePdfLocale(): string
    {
        $domainLocale = config('app.domain_locale', []);
        $host = request()->getHost();
        $hostVariants = array_unique(array_filter([
            $host,
            preg_replace('/^www\./i', '', $host),
            str_starts_with(strtolower($host), 'www.') ? null : 'www.'.$host,
        ]));
        foreach ($hostVariants as $h) {
            if ($h !== '' && isset($domainLocale[$h])) {
                return $domainLocale[$h];
            }
        }
        if (config('app.show_language_switcher', false) && session()->has('locale')) {
            return session('locale');
        }

        return config('app.locale', 'en');
    }

    public function invoice($id)
    {
        $previousLocale = app()->getLocale();
        $previousCarbonLocale = Carbon::getLocale();
        $locale = request('lang') ?: $this->resolveInvoicePdfLocale();
        App::setLocale($locale);
        Carbon::setLocale($locale);

        try {
        $bid = \App\Models\PostJobBid::with([
            'provider:id,display_name,address,vat_number,company_name',
            'customer:id,display_name,address,company_name,vat_number',
            'postrequest:id,title,price_type,job_price,total_days,total_hours,country_id,city_id',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'extraCharges',
        ])->findOrFail($id);
    
        // Optional: last payment (if you want to show context)
        $payment = \App\Models\PaymentPostJOb::where('post_job_bid_request_id', $bid->id)->latest('id')->first();
    
        $pdf = Pdf::loadView('postrequest.invoice', compact('bid', 'payment'))->setPaper('a4');
        $filename = 'post-bid-invoice-' . $bid->id . '.pdf';
        return $pdf->download($filename);
        } finally {
            App::setLocale($previousLocale);
            Carbon::setLocale($previousCarbonLocale);
        }
    }
    
    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';
        switch ($actionType) {
            case 'change-status':
                $branches = PostJobRequest::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk PostJobRequest Status Updated';
                break;

            case 'delete':
                PostJobRequest::whereIn('id', $ids)->delete();
                $message = 'Bulk PostJobRequest Deleted';
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
        $auth_user = authSession();
        $postJob = new PostJobRequest;
        // $pageTitle = __('messages.update_form_title',['form'=> __('messages.post_job')]);
        $pageTitle = __('messages.create_form_title', ['form' => __('messages.post_job')]);
        return view('post-job-request.create', compact('postJob', 'pageTitle', 'auth_user'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Increase execution time for large image processing
        set_time_limit(300); // 5 minutes for image processing
        
        //dd($request->all());
        // Basic validation to preserve old input on errors
        $request->validate([
            'title' => 'required|string|max:255',
            'country_id' => 'required|integer',
            'state_id' => 'required|integer',
            'city_id' => 'required|integer',
            'category_id' => 'required|integer',
            'subcategory_id' => 'required|integer',
            'price_type' => 'required|in:fixed,hourly,daily',
            'type' => 'required|in:onsite,remote,hybrid',
            'price' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'requirement' => 'required|string',
            'description' => 'required|string',
            'street_address' => 'required|string|max:500',
            'house_number' => 'required|string|max:255',
            'job_schedule' => 'required|in:full_time,part_time,contract,temporary,internship',
            'remote_work_level' => 'required|in:onsite,25_remote,50_remote,75_remote,100_remote',
            'career_level' => 'required|in:not_specified,entry_level,intermediate_level,experienced,professional,middle_management,executive_management,senior_management,director,technician,leader,manager',
            'travel_required' => 'required|in:0,1',
            'education_level' => 'required|in:not_specified,any_graduate,apprenticeship_degree,traineeship_degree,secondary_degree,undergraduate_diploma,high_school_graduate,associate_degree,college_degree,university_degree,bachelors_degree,masters_degree,doctorate_degree,professional_degree',
            'duties' => 'required|string',
            'benefits' => 'required|string',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480', // 20MB in KB
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480', // 20MB in KB
            'total_days' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
     //   dd(  $data);
        // store new address parts if present
        $data['street_address'] = $request->input('street_address');
        $data['house_number'] = $request->input('house_number');
        $data['customer_id'] = $request->customer_id ?? auth()->user()->id;

        // Normalize price_type -> job_price for backward compatibility
        if (isset($data['price_type'])) {
            $data['job_price'] = $data['price_type'];
        }

        // Enforce daily rule: hours = 8 * days
        if (($data['job_price'] ?? null) === 'daily') {
            $days = (int)($data['total_days'] ?? 0);
            $data['total_hours'] = $days * 8;
        }

        // Compute total_budget based on price type
        $price = (float)($data['price'] ?? 0);
        $type = $data['job_price'] ?? $data['price_type'] ?? 'fixed';
        $totalBudget = 0.0;
        if ($type === 'daily') {
            $totalBudget = $price * ((int)($data['total_days'] ?? 0));
        } elseif ($type === 'hourly') {
            $totalBudget = $price * ((int)($data['total_hours'] ?? 0));
        } else {
            $totalBudget = $price;
        }
        $data['total_budget'] = $totalBudget;

        // ✅ Handle image uploads (supports single and multiple) with resizing
        $imagePaths = [];
        $maxWidth = 1920; // Maximum width for images
        $maxHeight = 1920; // Maximum height for images
        $quality = 85; // JPEG quality (1-100)
        
        // Helper function to resize and save image
        $resizeAndSave = function($file, $filename) use ($maxWidth, $maxHeight, $quality) {
            try {
                // Increase memory limit for this operation
                $originalMemoryLimit = ini_get('memory_limit');
                ini_set('memory_limit', '256M');
                // Get image info
                $imageInfo = getimagesize($file->getRealPath());
                if (!$imageInfo) {
                    // If not a valid image, store as-is
                    return $file->storeAs('images', $filename, 'public');
                }
                
                $originalWidth = $imageInfo[0];
                $originalHeight = $imageInfo[1];
                $mimeType = $imageInfo['mime'];
                
                // Calculate new dimensions maintaining aspect ratio
                $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = (int)($originalWidth * $ratio);
                $newHeight = (int)($originalHeight * $ratio);
                
                // Only resize if image is larger than max dimensions
                if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                    return $file->storeAs('images', $filename, 'public');
                }
                
                // Create image resource based on mime type
                switch ($mimeType) {
                    case 'image/jpeg':
                        $sourceImage = imagecreatefromjpeg($file->getRealPath());
                        break;
                    case 'image/png':
                        $sourceImage = imagecreatefrompng($file->getRealPath());
                        break;
                    case 'image/gif':
                        $sourceImage = imagecreatefromgif($file->getRealPath());
                        break;
                    case 'image/webp':
                        if (function_exists('imagecreatefromwebp')) {
                            $sourceImage = imagecreatefromwebp($file->getRealPath());
                        } else {
                            return $file->storeAs('images', $filename, 'public');
                        }
                        break;
                    default:
                        return $file->storeAs('images', $filename, 'public');
                }
                
                if (!$sourceImage) {
                    return $file->storeAs('images', $filename, 'public');
                }
                
                // Create new image with calculated dimensions
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG and GIF
                if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                }
                
                // Resize image
                imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                
                // Save resized image
                $storagePath = storage_path('app/public/images');
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }
                $fullPath = $storagePath . '/' . $filename;
                
                switch ($mimeType) {
                    case 'image/jpeg':
                        imagejpeg($newImage, $fullPath, $quality);
                        break;
                    case 'image/png':
                        imagepng($newImage, $fullPath, 9);
                        break;
                    case 'image/gif':
                        imagegif($newImage, $fullPath);
                        break;
                    case 'image/webp':
                        if (function_exists('imagewebp')) {
                            imagewebp($newImage, $fullPath, $quality);
                        }
                        break;
                }
                
                // Free memory
                imagedestroy($sourceImage);
                imagedestroy($newImage);
                
                // Restore original memory limit
                ini_set('memory_limit', $originalMemoryLimit);
                
                return 'images/' . $filename;
            } catch (\Exception $e) {
                // Restore original memory limit on error
                if (isset($originalMemoryLimit)) {
                    ini_set('memory_limit', $originalMemoryLimit);
                }
                // If resizing fails, store original
                \Log::warning('Image resize failed: ' . $e->getMessage());
                return $file->storeAs('images', $filename, 'public');
            }
        };
        
        if ($request->hasFile('image')) {
            $incoming = $request->file('image');
            $files = is_array($incoming) ? $incoming : [$incoming];
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $path = $resizeAndSave($file, $filename);
                $imagePaths[] = $path;
                if ($idx === 0) {
                    $data['image'] = $path; // first image as cover
                }
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $img->getClientOriginalName());
                $path = $resizeAndSave($img, $filename);
                $imagePaths[] = $path;
            }
        }
        if (!empty($imagePaths)) {
            // remove duplicates and reindex
            $data['images'] = array_values(array_unique($imagePaths));
        }
        // ✅ Handle pre-uploaded cover image string (edge case)
        if ($request->has('image') && is_string($request->image) && empty($data['image'])) {
            $data['image'] = $request->image;
        }

        // Compose legacy working_address from parts if not provided
        if (empty($data['working_address'])) {
            $street = trim((string)($data['street_address'] ?? ''));
            $house  = trim((string)($data['house_number'] ?? ''));
            $composite = trim($street . ' ' . $house);
            if ($composite !== '') {
                $data['working_address'] = $composite;
            }
        }

        $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);

        // ✅ Handle services
        $result->postServiceMapping()->delete();
        if ($request->has('service_id') && is_array($request->service_id)) {
            $services = array_map(fn($service) => ['post_request_id' => $result->id, 'service_id' => $service], $request->service_id);
            $result->postServiceMapping()->insert($services);
        }

        // Fire provider notifications when a job is created or updated
        try {
            $this->sendNotification([
                'activity_type' => 'job_requested',
                'post_job' => $result,
            ]);
        } catch (\Throwable $e) {
            // Silent fail; do not block the flow on notification issues
        }

        $message = $result->wasRecentlyCreated ? __('messages.save_form', ['form' => __('messages.postrequest')])
            : __('messages.update_form', ['form' => __('messages.postrequest')]);

        return $request->is('api/*')
            ? comman_message_response($message)
            : redirect(route('post-job-request.index'))->withSuccess($message);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pageTitle = trans('messages.list_form_title', [
            'form' => trans('messages.postbid')
        ]);

        $auth_user = authSession();
        $assets = []; // you don’t need DataTable if loading directly

        // Build query
        $query = PostJobBid::with([
            'provider:id,display_name',
            'customer:id,display_name',
            // Include additional PostJobRequest fields and relations for the card
            'postrequest:id,title,customer_id,status,provider_id,remaining_percent,type,start_date,end_date,total_budget,city_id,country_id',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'postrequest.postBidList:id,post_request_id',
        ])->where('post_request_id', $id);

        // Restrict by user type
        if ($auth_user->user_type === 'user') {
            $query->whereHas('postrequest', function ($q) use ($auth_user) {
                $q->where('customer_id', $auth_user->id);
            });
        }

        $bids = $query->get();
        $averageBid = (float) ($bids->avg('price') ?? 0);

        return view('postrequest.view', compact('pageTitle', 'auth_user', 'assets', 'id', 'bids', 'averageBid'));
    }


    // public function postrequest_index_data(DataTables $datatable,$id)
    // {
    //     $query = PostJobBid::where('post_request_id',$id);

    //     if (auth()->user()->hasAnyRole(['admin'])) {
    //         $query->newquery();
    //     }

    //     return $datatable  ->eloquent($query)
    //     ->editColumn('post_request_id' , function ($post_job_bid){
    //         return ($post_job_bid->post_request_id != null && isset($post_job_bid->postrequest)) ? $post_job_bid->postrequest->title : '-';
    //     })
    //     ->editColumn('provider_id' , function ($post_job_bid){
    //         return ($post_job_bid->provider_id != null && isset($post_job_bid->provider)) ? $post_job_bid->provider->display_name : '-';
    //     })
    //     ->editColumn('customer_id', function ($post_job_bid){
    //         return ($post_job_bid->customer_id != null && isset($post_job_bid->customer)) ? $post_job_bid->customer->display_name : '-';
    //     })
    //     ->editColumn('price' , function ($post_job){
    //         return getPriceFormat($post_job->price);
    //     })
    //     ->editColumn('duration' , function ($post_job_bid){
    //         return ($post_job_bid->duration != null) ? $post_job_bid->duration : '-';
    //     })
    //     ->addIndexColumn()
    //     ->toJson();
    // }
    public function postrequest_index_data(DataTables $datatable, $id)
    {
        $query = PostJobBid::where('post_request_id', $id)
            ->with(['provider', 'customer', 'postrequest']);

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable->eloquent($query)
            // ID column
            ->addColumn('id', function ($bid) {
                return $bid->id;
            })
            // Title column
            ->addColumn('title', function ($bid) {
                return optional($bid->postrequest)->title ?? '-';
            })
            // Posted at
            ->addColumn('created_at', function ($bid) {
                return $bid->created_at ? $bid->created_at->format('Y-m-d') : '-';
            })
            // Max. Budget (from post request total_budget) with price_type suffix
            ->addColumn('price', function ($bid) {
                $post = $bid->postrequest;
                if (!$post) {
                    return '-';
                }
                $budget = $post->price;
                $amount = $budget !== null ? getPriceFormat($budget) : '-';
                $type = strtolower((string)($post->price_type ?? $post->job_price ?? 'fixed'));
                $label = $type === 'hourly' ? 'hourly' : ($type === 'daily' ? 'daily' : ($type === 'fixed' ? 'fixed' : $type));
                return trim($amount . ' ' . $label);
            })
            // Start Date / End Date (date-only)
            ->addColumn('start_date', function ($bid) {
                $d = optional($bid->postrequest)->start_date;
                return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : '-';
            })
            ->addColumn('end_date', function ($bid) {
                $d = optional($bid->postrequest)->end_date;
                return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : '-';
            })
            // Provider name
            ->addColumn('provider', function ($bid) {
                return optional($bid->provider)->display_name ?? '-';
            })
            // Why Choose Me (raw HTML content for modal display)
            ->addColumn('why_choose_me', function ($bid) {
                return (string) ($bid->why_choose_me ?? '');
            })
            // Bid Amount with price_type suffix (from post request)
            ->addColumn('bid_amount', function ($bid) {
                $amount = getPriceFormat($bid->price);
                $post = $bid->postrequest;
                $type = $post ? strtolower((string)($post->price_type ?? $post->job_price ?? 'fixed')) : 'fixed';
                $label = $type === 'hourly' ? 'hourly' : ($type === 'daily' ? 'daily' : ($type === 'fixed' ? 'fixed' : $type));
                return trim($amount . ' ' . $label);
            })
            // Status label with colors
            ->addColumn('status', function ($bid) {
                $key = strtolower((string)($bid->status ?? ''));
                $map = [
                    'requested' => ['Requested', 'badge bg-secondary text-white'],
                    'accepted' => ['Accepted', 'badge bg-info text-white'],
                    'advance_paid' => ['Advance Paid', 'badge bg-primary text-white'],
                    'in_progress' => ['IN Progress', 'badge bg-primary text-white'],
                    'confirm_done' => ['Confirm Done', 'badge bg-success text-white'],
                    'completed' => ['Completed', 'badge bg-success text-white'],
                    'cancelled' => ['Cancelled', 'badge bg-danger text-white'],
                    'rejected' => ['Rejected', 'badge bg-danger text-white'],
                    'hold' => ['On Hold', 'badge bg-warning text-dark'],
                ];
                if (isset($map[$key])) { [$label,$cls] = $map[$key]; }
                else { $label = ucwords(str_replace('_',' ', (string)$bid->status)); $cls='badge bg-secondary text-white'; }
                return '<span class="'.$cls.'">'.e($label).'</span>';
            })
            // Action: View Job (go to the specific bid details)
            ->addColumn('action', function ($bid) {
                $bidId = $bid->id;
                $url = route('post-job-bid.showByBid', ['bidId' => $bidId]);
                return '<a href="' . $url . '" class="btn btn-sm btn-outline-primary"><i class="far fa-eye"></i> View Job</a>';
            })
            ->addIndexColumn()
            ->editColumn('provider_id', function ($bid) {
                return optional($bid->provider)->display_name ?? ($bid->provider_id ?: '-');
            })
            ->editColumn('customer_id', function ($bid) {
                return optional($bid->customer)->display_name ?? ($bid->customer_id ?: '-');
            })
            ->rawColumns(['action','status'])
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
        if (demoUserPermission()) {
            if (request()->is('api/*')) {
                return comman_message_response(__('messages.demo_permission_denied'));
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $post_request = PostJobRequest::find($id);
        //$post_request->delete();
        $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.postrequest')]);

        if ($post_request != '') {
            if ($post_request->postServiceMapping()->count() > 0) {
                $post_request->postServiceMapping()->delete();
            }
            if ($post_request->postBidList()->count() > 0) {
                $post_request->postBidList()->delete();
            }
            $post_request->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.postrequest')]);
        }
        if (request()->is('api/*')) {
            return comman_custom_response(['message' => $msg, 'status' => true]);
        }
        return redirect()->back()->withSuccess($msg);
    }

    public function viewPostBids($id)
    {
        $auth_user = authSession();
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.postbid')]);
        $assets = ['datatable'];
        return view('postrequest.bids', compact('pageTitle', 'auth_user', 'assets', 'id'));
    }

    public function startWork(Request $request, $id)
    {
        $request->validate([
            'advance_percent' => 'required|integer|min:20|max:99',
            'remaining_percent' => 'required|integer|min:1|max:80',
        ], [
            'advance_percent.min' => 'Advance must be between 20% and 99%.',
            'advance_percent.max' => 'Advance must be between 20% and 99%.',
        ]);

        // Find the bid
        $bid = PostJobBid::findOrFail($id);

        // Update advance, remaining, and status
        $bid->advance_percent = $request->input('advance_percent');
        $bid->remaining_percent = $request->input('remaining_percent');
        $bid->status = 'Advance Payment Pending';
        $bid->save();

        // Notify customer: Employer set payment split
        try {
            $bid->load(['postrequest', 'provider', 'customer']);
            $this->sendNotification([
                'activity_type'    => 'post_job_bid_status_update',
                'post_job'         => $bid,
                'bid_status'       => 'Payment split set',
                'notify_recipient' => 'user',
            ]);
        } catch (\Throwable $e) {
            \Log::warning('startWork (split payment) notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => ' payment split updated successfully!'
        ]);
    }
}
