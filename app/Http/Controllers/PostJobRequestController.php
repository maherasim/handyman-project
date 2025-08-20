<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use Yajra\DataTables\DataTables;
use App\Models\Service;
use App\Models\SubCategory;
use App\Models\Setting;

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
 public function bidshowindex()
{
    
    $auth_user = authSession();


$assignedPost = PostJobRequest::where('provider_id', $auth_user->id)
    ->where('status', 'assigned')
    ->first();


    // If the viewer is a customer, get their latest assigned/in_progress job
     

    $postJobBids = PostJobBid::where('provider_id', $auth_user->id)->get();

    $pageTitle = trans('messages.list_form_title', ['form' => trans('messages.postbid')]);
    $assets = ['datatable'];

    return view('postrequest.view', compact(
        'pageTitle', 'auth_user', 'assets', 'postJobBids', 'assignedPost'));
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

public function bidshow()
{
    $auth_user = authSession();

    $query = PostJobBid::query()->with([
        'provider:id,display_name',
        'customer:id,display_name',
        'postrequest:id,title,customer_id,status,provider_id,remaining_percent'
    ]);

    if ($auth_user->user_type === 'provider') {
        // Providers see their assigned/started posts
        $query->where('provider_id', $auth_user->id);
    } elseif ($auth_user->user_type === 'user') {
        // Users see only their posts which are in_progress
        $query->whereHas('postrequest', function ($q) use ($auth_user) {
            $q->where('customer_id', $auth_user->id)
              ->where('status', 'in_progress'); // ✅ only in_progress
        });
         dd($query->toSql(), $query->getBindings()); // SQL & bindings

    }

$postJobBids = $query->get()->filter(function($bid) use ($auth_user) {
    if ($auth_user->user_type === 'user') {
        return $bid->postrequest && $bid->postrequest->status === 'in_progress';
    }
    return true; // provider sees all their assigned/started bids
});

    return DataTables::of($postJobBids)
        ->addIndexColumn()
        ->addColumn('provider_name', fn($bid) => $bid->provider->display_name ?? 'N/A')
        ->addColumn('customer_name', fn($bid) => $bid->customer->display_name ?? 'N/A')
        ->addColumn('post_title', fn($bid) => $bid->postrequest->title ?? 'N/A')
        ->addColumn('status', fn($bid) => $bid->postrequest->status ?? 'N/A')
        ->addColumn('action', function ($bid) use ($auth_user) {
            $post = $bid->postrequest;

            // Provider: Start Work
            if ($auth_user->user_type === 'provider' && $post && $post->status === 'assigned' && $post->provider_id == $bid->provider_id) {
                return '<button class="btn btn-sm btn-primary startWorkBtn" data-post-id="'.$post->id.'">Start Work</button>';
            }

            // Customer: Pay Advance
            if ($auth_user->user_type === 'user' && $post && $post->status === 'in_progress') {
                return '<button class="btn btn-sm btn-success payAdvanceBtn" 
                            data-post-id="'.$post->id.'" 
                            data-amount="'.$post->remaining_percent.'">
                            <i class="fas fa-credit-card"></i> Pay Advance ('.$post->remaining_percent.')</button>';
            }

            return '-';
        })
        ->rawColumns(['action'])
        ->toJson();
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

    $bid = PostJobBid::with('postrequest')->findOrFail($id);

    // Ensure customer owns this job request
    if (!$bid->postrequest || (int)$auth_user->id !== (int)$bid->postrequest->customer_id) {
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    }

    // Assign provider to the post job request and update status
    $post = $bid->postrequest;
    $post->provider_id = $bid->provider_id;
    $post->status = 'assigned';
    $post->save();

    // Optionally, notify the provider/user about assignment
    try {
        $this->sendNotification([
            'activity_type' => 'user_accept_bid',
            // 'post_job' => $post,
            // Provide the accepted bid price for templates/notifications
            // 'job_price' => getPriceFormat($bid->price),
        ]);
    } catch (\Throwable $e) {
        // Silent fail for notifications
    }

    return response()->json(['status' => true, 'message' => 'Bid accepted and job assigned successfully!']);
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
        
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.postbid')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        $jobpost = null;
        if ($auth_user->user_type === 'user') {
            $jobpost = PostJobRequest::where('customer_id', $auth_user->id)
                ->whereIn('status', ['assigned','in_progress'])
                ->latest()
                ->first();
        }
        return view('postrequest.view', compact('pageTitle', 'auth_user', 'assets','id'));
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

    public function startWork(Request $request, $id)
    {
        $post = PostJobRequest::findOrFail($id);
        if (!auth()->user()->hasAnyRole(['provider'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        // Provider starts work on assigned post request
        if ($post->status !== 'assigned') {
            return response()->json(['message' => 'Job is not assigned'], 400);
        }
        $post->status = 'in_progress';
        $post->save();

        // Notify user about start
        try {
            $this->sendNotification([
                'activity_type' => 'update_booking_status',
                'post_job' => $post,
            ]);
        } catch (\Throwable $e) {
        }

        return response()->json(['status' => true, 'message' => 'Work started']);
    }
}
