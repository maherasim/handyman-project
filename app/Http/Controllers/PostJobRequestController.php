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
        
        // Fetch all bids that belong to the logged-in provider
        $postJobBids = PostJobBid::where('provider_id', $auth_user->id)->get();
    
        $pageTitle = trans('messages.list_form_title', ['form' => trans('messages.postbid')]);
        $asset = ['datatable'];
    
        return view('postrequest.view', compact('pageTitle', 'auth_user', 'asset', 'postJobBids'));
    }
    public function bidshow()
    {
        $auth_user = authSession();
    
        // Fetch all bids that belong to the logged-in provider and load provider data
        $postJobBids = PostJobBid::where('provider_id', $auth_user->id)
        ->with(['provider:id,display_name', 'customer:id,display_name', 'postrequest:id,title', ])
        ->get();
     
        return DataTables::of($postJobBids)
            ->addIndexColumn()
            ->addColumn('provider_name', function ($postJobBid) {
                return $postJobBid->provider->display_name ?? 'N/A';
            })
            ->addColumn('customer_name', function ($postJobBid) {
                return $postJobBid->customer->display_name ?? 'N/A';
            })
            ->addColumn('post_title', function ($postJobBid) {
                return $postJobBid->postrequest->title ?? 'N/A';
            })
            ->toJson();
    }


    public function index_data(DataTables $datatable,Request $request)
    {
        $query = PostJobRequest::query();
        $filter = $request->filter;
        $query->whereDoesntHave('postBidList', function($postBidList){
            $postBidList->where('provider_id', auth()->user()->id);
        });

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
            ->editColumn('title', function($query) {
                return $query->title; // Return plain text instead of a clickable link
            })
            
            ->editColumn('provider_id' , function ($query){
                return view('postrequest.provider', compact('query'));
            })
            ->editColumn('customer_id' , function ($query){
                return view('postrequest.customer', compact('query'));
            })
            ->filterColumn('customer_id',function($query,$keyword){
                $query->whereHas('customer',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('post_job_requests.*')
                      ->join('users as customers', 'customers.id', '=', 'post_job_requests.customer_id')
                      ->orderBy('customers.display_name', $order);   
            })
            ->editColumn('price' , function ($query){
                return getPriceFormat($query->price);
            })
            ->editColumn('status' , function ($query){
               $status = $query->status;
                if($status == 'requested'){
                    $status = '<span class="badge text-primary bg-primary-subtle">'.__('messages.requested').'</span>';
                }
                return  $status;
            })

            ->addColumn('action', function($post_job){
                return view('postrequest.action',compact('post_job'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title','action','status','check'])
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
        $data = $request->all();
        $data['customer_id'] = $request->customer_id ?? auth()->user()->id;
    
        // ✅ Handle single image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image'); // Get the file (not an array)
    
            if (is_array($image)) {
                $image = $image[0]; // If mistakenly sent as an array, get the first one
            }
    
            $filename = time() . '_' . $image->getClientOriginalName(); // Unique filename
            $path = $image->storeAs('images', $filename, 'public'); // Store in storage/app/public/images
            $data['image'] = $path; // Save file path in the database
        }
    
        // ✅ Handle multiple image uploads (if needed)
        if ($request->hasFile('images')) { // Assuming multiple images are sent as 'images' key
            $imagePaths = [];
    
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . $img->getClientOriginalName(); // Unique filename
                $path = $img->storeAs('images', $filename, 'public');
                $imagePaths[] = $path;
            }
    
            $data['images'] = json_encode($imagePaths, JSON_UNESCAPED_SLASHES); // Store array in DB
        }
    
        // ✅ Handle pre-uploaded image (if sent as a string)
        if ($request->has('image') && is_string($request->image)) {
            $data['image'] = $request->image;
        }
    
        $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);
    
        // ✅ Send notification
        $this->sendNotification([
            'activity_type' => $request->status == 'assigned' ? 'user_accept_bid' : 'job_requested',
            'post_job_id' => $result->id,
            'customer_name' => optional($result->customer)->display_name,
            'provider_name' => optional($result->provider)->display_name,
            'latitude' => $data['latitude'] ?? 0.0,
            'longitude' => $data['longitude'] ?? 0.0,
        ]);
    
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
        $asset = ['datatable'];
        return view('postrequest.view', compact('pageTitle', 'auth_user', 'asset','id'));
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
}
