<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use Yajra\DataTables\DataTables;
use App\Models\Service;
use App\Models\WalletHistory;
use App\Models\Payment;
use App\Models\SubCategory;
use App\Models\Wallet;
use App\Models\Setting;
use DB;
use Carbon\Carbon;

use App\Models\ProviderPayout;
use App\Models\CommissionEarning;
use App\Models\PostJobBid;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Log;

class PostJobRequestController extends Controller
{
    use NotificationTrait;
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

        return view('postrequest.index', compact('pageTitle','auth_user','assets','filter'));

    }
    public function showbid($id)
{
    $bid = PostJobBid::with('request')
        ->where('id', $id)
        ->where('status', 'accepted')
        ->firstOrFail();

    return view('post-job-bid.show', compact('bid'));
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
        'pageTitle', 'auth_user', 'assets', 'postJobBids', 'assignedPost','advance_payment'));
}

 public function setAdvanceSplit(Request $request, $id)
    {
        $request->validate([
            'advance_percent' => 'required|integer|min:0|max:100',
            'remaining_percent' => 'required|integer|min:0|max:100',
        ]);

        $post = PostJobRequest::findOrFail($id);
        $post->advance_percent = $request->advance_percent;
        $post->remaining_percent = $request->remaining_percent;
        $post->status = 'in_progress';
        $post->save();

        return response()->json(['status' => true, 'message' => 'Payment split set & work started.']);
    }
public function bidshow(Request $request)
{
    $auth_user = authSession();

    $query = PostJobBid::query()->with([
        'provider:id,display_name',
        'customer:id,display_name',
        // Include required PostJobRequest fields for card details
        'postrequest:id,title,customer_id,status,provider_id,remaining_percent,type,start_date,end_date,total_budget,city_id,country_id',
        // Eager-load location and bids for counts
        'postrequest.city:id,name',
        'postrequest.country:id,name',
        'postrequest.postBidList:id,post_request_id',
    ]);

    if ($auth_user->user_type === 'provider') {
        $query->where('provider_id', $auth_user->id);
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
        ->addColumn('provider_name', fn($bid) => $bid->provider->display_name ?? 'N/A')
        ->addColumn('customer_name', fn($bid) => $bid->customer->display_name ?? 'N/A')
        ->addColumn('post_title', fn($bid) => $bid->postrequest->title ?? 'N/A')
        // Extra fields for cards
        ->addColumn('city', function($bid){
            $post = $bid->postrequest;
            return ($post && $post->city) ? ($post->city->name ?? 'N/A') : 'N/A';
        })
        ->addColumn('country', function($bid){
            $post = $bid->postrequest;
            return ($post && $post->country) ? ($post->country->name ?? 'N/A') : 'N/A';
        })
        ->addColumn('job_type', function($bid){
            return $bid->postrequest->type ?? null;
        })
        ->addColumn('start_date', function($bid){
            return $bid->postrequest->start_date ?? null;
        })
        ->addColumn('end_date', function($bid){
            return $bid->postrequest->end_date ?? null;
        })
        ->addColumn('total_budget', function($bid){
            return $bid->postrequest->total_budget ?? null;
        })
        ->addColumn('applications', function($bid){
            $post = $bid->postrequest;
            if (!$post) { return 0; }
            // Use eager-loaded collection to avoid N+1
            return isset($post->postBidList) ? $post->postBidList->count() : $post->postBidList()->count();
        })
        ->addColumn('status', fn($bid) => $bid->status ?? 'N/A')
        ->addColumn('hold_reason', fn($bid) => $bid->hold_reason ?? null)
        ->addColumn('has_advance_paid', function($bid) {
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
        ->addColumn('action', function($bid) use ($auth_user) {
            $post = $bid->postrequest;

            if (!$post) return '-';

            // Provider: Start Work if assigned
            // if ($auth_user->user_type === 'provider' && $post->status === 'assigned' && $post->provider_id == $bid->provider_id) {
            //     return '<button class="btn btn-sm btn-primary startWorkBtn" data-post-id="'.$post->id.'">Start Work</button>';
            // }

            // Customer: Accept bid if requested
            if ($auth_user->user_type === 'user' && $post->status === 'requested' && $bid->status !== 'accepted') {
                return '<button class="btn btn-sm btn-success acceptBid" data-id="'.$bid->id.'">Accept</button>';
            }

            // Provider: show Start Work when advance is paid on bid
            if ($auth_user->user_type === 'provider' && (int)$auth_user->id === (int)$bid->provider_id && $bid->status === 'advance_paid') {
                return '<button class="btn btn-sm btn-primary updateStatusBtn" data-id="'.$bid->id.'" data-status="in_progress">Start Work</button>';
            }

            // Customer: show Let's Start Work when already in progress (idempotent)
            if ($auth_user->user_type === 'user' && (int)$auth_user->id === (int)$bid->customer_id && in_array($bid->status, ['in_progress','in_process'])) {
                return '<button class="btn btn-sm btn-info updateStatusBtn" data-id="'.$bid->id.'" data-status="in_process">Let\'s Start Work</button>';
            }

            // Provider: when in progress or in process, show Hold and Done
            if ($auth_user->user_type === 'provider' && (int)$auth_user->id === (int)$bid->provider_id && in_array($bid->status, ['in_progress','in_process','hold'])) {
                $buttons = [];
                $buttons[] = '<button class="btn btn-sm btn-warning holdBidBtn" data-id="'.$bid->id.'">Hold</button>';
                $buttons[] = '<button class="btn btn-sm btn-success updateStatusBtn" data-id="'.$bid->id.'" data-status="done">Done</button>';
                return implode(' ', $buttons);
            }

            // Otherwise
            return $bid->status === 'accepted'
                ? '<span class="badge badge-success">Accepted</span>'
                : '-';
        })
        ->rawColumns(['action'])
        ->toJson();
}
public function payAdvance(Request $request, $id)
{
    $user = auth()->user();
    $post = PostJobBid::findOrFail($id);

    // Determine advance amount
    $requestedAmount = $request->input('amount');
    $advanceAmount = empty(trim((string)$requestedAmount))
        ? ($post->price * $post->advance_percent / 100)
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
        // Create one transaction ID for both debit & credit
        $txnId = uniqid("ADV-");

        /*
        |--------------------------------------------------------------------------
        | Debit from Customer Wallet
        |--------------------------------------------------------------------------
        */
        $wallet->decrement('amount', $advanceAmount);

        // Wallet history (customer)
        WalletHistory::create([
            'datetime'        => now(),
            'user_id'         => $user->id,
            'activity_type'   => 'debit',
            'activity_message'=> "Advance payment for Bid #{$post->id}",
            'activity_data'   => json_encode([
                'amount'  => $advanceAmount,
                'balance' => $wallet->amount,
            ]),
        ]);

        // Payment entry (customer)
        Payment::create([
            'customer_id'             => $user->id,
            'booking_id'              => null, 
            'datetime'                => now(),
            'post_job_request_id'     => $post->id,
            'discount'                => 0,
            'total_amount'            => $advanceAmount,
            'payment_type'            => 'wallet',
            'txn_id'                  => $txnId,
            'payment_status'          => 'completed',
            'other_transaction_detail'=> json_encode([
                'type'     => 'advance',
                'bid_id'   => $post->id,
                'provider' => $post->provider_id,
            ]),
        ]);

        // Update post status
        $post->status = 'advance_paid';
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

        $adminCommissionAmount = ($advanceAmount * $adminCommissionPercent) / 100.0;
        $providerPayoutAmount  = max(0, $advanceAmount - $adminCommissionAmount);

        if ($providerPayoutAmount > 0) {
            $providerWallet = Wallet::firstOrCreate(['user_id' => $post->provider_id]);
            $providerWallet->increment('amount', $providerPayoutAmount);

            // Wallet history (provider)
            WalletHistory::create([
                'datetime'        => now(),
                'user_id'         => $post->provider_id,
                'activity_type'   => 'credit',
                'activity_message'=> "Advance received for Bid #{$post->id}",
                'activity_data'   => json_encode([
                    'amount'  => $providerPayoutAmount,
                    'balance' => $providerWallet->amount,
                ]),
            ]);

            // Payment entry (provider)
            Payment::create([
                'customer_id'             => $post->provider_id,
                'booking_id'              => null, 
                'datetime'                => now(),
                'post_job_request_id'     => $post->id,
                'discount'                => 0,
                'total_amount'            => $providerPayoutAmount,
                'payment_type'            => 'wallet',
                'txn_id'                  => $txnId, // same txnId links debit & credit
                'payment_status'          => 'completed',
                'other_transaction_detail'=> json_encode([
                    'type'     => 'advance_payout',
                    'bid_id'   => $post->id,
                    'customer' => $user->id,
                ]),
            ]);

            // Save provider payout record
            ProviderPayout::create([
                'provider_id'    => $post->provider_id,
                'amount'         => $providerPayoutAmount,
                'payment_method' => 'wallet',
                'paid_date'      => now(),
                'status'         => 'advance paid',
                'description'    => "Advance payout for Bid #{$post->id}",
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
                'employee_id'       => 1,
                'commission_amount' => $adminCommissionAmount,
                'commission_status' => 'paid',
            ]);
        }

        if ($providerPayoutAmount > 0) {
            CommissionEarning::create([
                'user_type'         => 'provider',
                'employee_id'       => $post->provider_id,
                'commission_amount' => $providerPayoutAmount,
                'commission_status' => 'paid',
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => "Advance payment of €{$advanceAmount} successful",
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

    // Set new status
    $bid->status = $request->input('status');

    // If hold → save reason
    if ($bid->status === 'hold' && $request->filled('hold_reason')) {
        $bid->hold_reason = $request->input('hold_reason');
    }

    
    $bid->save();

    try {
        $this->sendNotification([
            'activity_type' => 'update_booking_status',
            'post_job' => $bid,
        ]);
    } catch (\Throwable $e) {
        // silent fail for notifications
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
    $request->validate([
        'title'    => 'required|string|max:255',
        'amount'   => 'required|numeric|min:0.01',
        'quantity' => 'nullable|integer|min:1'
    ]);

    $bid = PostJobBid::findOrFail($id);

    $quantity = (int)($request->input('quantity') ?? 1);
    $increment = ((float)$request->input('amount')) * max(1, $quantity);

    $bid->price = ((float)$bid->price) + $increment;
    $bid->save();

    return response()->json([
        'status'  => true,
        'message' => 'Extra charges added successfully',
        'price'   => $bid->price,
    ]);
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


public function acceptBid($id)
{
    $auth_user = authSession();

    // Load the bid with its post request
    $bid = PostJobBid::with('postrequest')->findOrFail($id);

    // Ensure customer owns this job request
    if (!$bid->postrequest || (int)$auth_user->id !== (int)$bid->postrequest->customer_id) {
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    }

    // Update the bid status instead of post request
    $bid->status = 'accepted';
    $bid->save();

    // Optionally, assign the provider to the post request
    // $post = $bid->postrequest;
    // if ($post) {
    //     $post->provider_id = $bid->provider_id;
    //     $post->status = 'assigned'; // This is optional if you still want post request status to reflect assignment
    //     $post->save();
    // }

    // Notify provider/user if needed
    try {
        $this->sendNotification([
            'activity_type' => 'user_accept_bid',
            // 'post_job' => $post,
            // 'job_price' => getPriceFormat($bid->price),
        ]);
    } catch (\Throwable $e) {
        // Ignore notification failures
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

    // Exclude entries where the provider has already bid
    $query->whereDoesntHave('postBidList', function($postBidList){
        $postBidList->where('provider_id', auth()->id());
    });

    if (isset($filter)) {
        if (isset($filter['column_status'])) {
            $query->where('status', $filter['column_status']);
        }
    }

    return $datatable->eloquent($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
        })
        ->editColumn('title', function($query) {
            return $query->title;
        })
        ->editColumn('provider_id', function ($query){
            return view('postrequest.provider', compact('query'));
        })
        ->editColumn('customer_id', function ($query){
            return view('postrequest.customer', compact('query'));
        })
        ->filterColumn('customer_id', function($query, $keyword){
            $query->whereHas('customer', function ($q) use($keyword){
                $q->where('display_name', 'like', '%'.$keyword.'%');
            });
        })
        ->orderColumn('customer_id', function ($query, $order) {
            $query->select('post_job_requests.*')
                ->join('users as customers', 'customers.id', '=', 'post_job_requests.customer_id')
                ->orderBy('customers.display_name', $order);
        })
        ->editColumn('price', function ($query){
            return getPriceFormat($query->price);
        })
        ->editColumn('status', function ($query){
            $status = $query->status;
            if ($status == 'requested') {
                $status = '<span class="badge text-primary bg-primary-subtle">'.__('messages.requested').'</span>';
            }
            return $status;
        })
        ->addColumn('action', function($post_job){
            return view('postrequest.action', compact('post_job'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['title', 'action', 'status', 'check'])
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
        $pageTitle = __('messages.create_form_title',['form'=> __('messages.post_job')]);
        return view('post-job-request.create',compact('postJob','pageTitle','auth_user'));

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            'description' => 'nullable|string',
            'job_schedule' => 'required|in:full_time,part_time,contract,temporary,internship',
            'remote_work_level' => 'required|in:onsite,25_remote,50_remote,75_remote,100_remote',
            'career_level' => 'required|in:intern,entry,junior,mid,senior,lead,manager',
            'travel_required' => 'required|in:0,1',
            'education_level' => 'required|in:high_school,associate,undergraduate,graduate,doctorate',
            'duties' => 'nullable|string',
            'benefits' => 'nullable|string',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'total_days' => 'nullable|integer|min:0',
            'total_hours' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
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
    
        // ✅ Handle image uploads (supports single and multiple)
        $imagePaths = [];
        if ($request->hasFile('image')) {
            $incoming = $request->file('image');
            $files = is_array($incoming) ? $incoming : [$incoming];
            foreach ($files as $idx => $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('images', $filename, 'public');
                $imagePaths[] = $path;
                if ($idx === 0) {
                    $data['image'] = $path; // first image as cover
                }
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . $img->getClientOriginalName();
                $path = $img->storeAs('images', $filename, 'public');
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
    
        $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);
    
        // ✅ Handle services
        $result->postServiceMapping()->delete();
        if ($request->has('service_id') && is_array($request->service_id)) {
            $services = array_map(fn($service) => ['post_request_id' => $result->id, 'service_id' => $service], $request->service_id);
            $result->postServiceMapping()->insert($services);
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
    if ($auth_user->user_type === 'provider') {
        $query->where('provider_id', $auth_user->id);
    } elseif ($auth_user->user_type === 'user') {
        $query->whereHas('postrequest', function ($q) use ($auth_user) {
            $q->where('customer_id', $auth_user->id);
        });
    }

    $bids = $query->get();

    return view('postrequest.view', compact('pageTitle', 'auth_user', 'assets', 'id', 'bids'));
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
    public function postrequest_index_data(DataTables $datatable,$id)
    {
        $query = PostJobBid::where('post_request_id',$id);

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newquery();
        }

        return $datatable  ->eloquent($query)
        ->editColumn('post_request_id' , function ($post_job_bid){
            return ($post_job_bid->post_request_id != null && isset($post_job_bid->postrequest)) ? $post_job_bid->postrequest->title : '-';
        })
        ->editColumn('provider_id' , function ($post_job_bid){
            return ($post_job_bid->provider_id != null && isset($post_job_bid->provider)) ? $post_job_bid->provider->display_name : '-';
        })
        ->editColumn('customer_id', function ($post_job_bid){
            return ($post_job_bid->customer_id != null && isset($post_job_bid->customer)) ? $post_job_bid->customer->display_name : '-';
        })
        ->editColumn('price' , function ($post_job){
            return getPriceFormat($post_job->price);
        })
        ->editColumn('duration' , function ($post_job_bid){
            return ($post_job_bid->duration != null) ? $post_job_bid->duration .' hours' : '-';
        })
        ->addIndexColumn()
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
            if(request()->is('api/*')){
                return comman_message_response( __('messages.demo_permission_denied') );
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $post_request = PostJobRequest::find($id);
        //$post_request->delete();
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.postrequest')] );

        if($post_request!='') {
            if($post_request->postServiceMapping()->count() > 0)
            {
                $post_request->postServiceMapping()->delete();
            }
            if($post_request->postBidList()->count() > 0)
            {
                $post_request->postBidList()->delete();
            }
            $post_request->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.postrequest')] );
        }
        if(request()->is('api/*')){
            return comman_custom_response(['message'=> $msg , 'status' => true]);
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
    // Find the bid
    $post = PostJobBid::findOrFail($id);

    // Ensure user is a provider
     
 

    // Update advance, remaining, and status
    $post->advance_percent = $request->input('advance_percent');
    $post->remaining_percent = $request->input('remaining_percent');
    $post->status = 'advance_payment';
    $post->save();

    // Optionally notify the user
    try {
        $this->sendNotification([
            'activity_type' => 'update_booking_status',
            'post_job' => $post,
        ]);
    } catch (\Throwable $e) {
        // Silent fail
    }

    return response()->json([
        'status' => true, 
        'message' => ' payment split updated successfully!'
    ]);
}

}
