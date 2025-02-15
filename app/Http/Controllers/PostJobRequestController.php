<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use Yajra\DataTables\DataTables;
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
        $pageTitle = trans('messages.customer_job_request');
        $auth_user = authSession();
        $assets = ['datatable'];

        return view('postrequest.index', compact('pageTitle','auth_user','assets','filter'));

    }


    public function index_data(DataTables $datatable,Request $request)
    {
        $query = PostJobRequest::query()->orderBy('created_at', 'desc');
        $filter = $request->filter;
        $auth_user = authSession();
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', 'like', '%'.$filter['column_status'].'%');
            }
        }
        if ($auth_user->user_type == 'user') {
            $query->where('customer_id', auth()->user()->id);
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';

            })
            ->editColumn('title', function($query){
                if (authSession()->user_type == 'user') {
                    return $query->title;
                } else {
                    // return '<a class="btn-link btn-link-hover"  href='.route('postjobrequest.service', $query->id).'>'.$query->title.'</a>';
                    return '<a class="btn-link">'.$query->title.'</a>';
                }
                // return '<a class="btn-link btn-link-hover"  href='.route('postjobrequest.service',$query->id).'>'.$query->title.'</a>';
            })
            ->editColumn('provider_id' , function ($query){
                return ($query->provider_id != null && isset($query->provider)) ? $query->provider->display_name : '-';
            })
            ->editColumn('customer_id' , function ($query){
                return ($query->customer_id != null && isset($query->customer)) ? $query->customer->display_name : '-';
            })
            ->filterColumn('customer_id',function($query,$keyword){
                $query->whereHas('customer',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('price' , function ($query){
                return getPriceFormat($query->price);
            })
            ->editColumn('status' , function ($query){
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" data-type="postjobbid_status" '.($query->status ? "checked" : "").' value="'.$query->id.'" id="'.$query->id.'" data-id="'.$query->id.'" >
                        <label class="custom-control-label" for="'.$query->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->editColumn('is_featured' , function ($query){
                // $disabled = $query->trashed() ? 'disabled': '';

                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" data-type="post_job_featured" data-name="is_featured" '.($query->is_featured ? "checked" : "").' value="'.$query->id.'" id="f'.$query->id.'" data-id="'.$query->id.'">
                        <label class="custom-control-label" for="f'.$query->id.'" data-on-label="'.__("messages.yes").'" data-off-label="'.__("messages.no").'"></label>
                    </div>
                </div>';
            })
            ->addColumn('action', function($post_job){
                // if ($post_job->provider_id != null && $post_job->id != null) {
                //     $postbid= PostJobBid::where('provider_id', $post_job->provider_id)->where('post_request_id',$post_job->id)->first();
                // }
                return view('postrequest.action',compact('post_job'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title','action','status','check','is_featured'])
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
    // public function store(Request $request)
    // {
    //     Log::info('Received request:', $request->all());
    // // Existing code...

    // // Log::info('post_request_id:', $request->post_request_id);
    // // Log::info('service_ids:', $request->service_ids);

    //     $data = $request->all();
    //     $data['customer_id'] =  !empty($request->customer_id) ? $request->customer_id : auth()->user()->id;
    //     $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);
    //     $activity_data = [
    //         'activity_type' => 'job_requested',
    //         'post_job_id' => $result->id,
    //         'post_job' => $result,
    //         'latitude' =>isset($data['latitude']) ? $data['latitude'] : 0.0,
    //         'longitude' => isset($data['longitude']) ? $data['longitude'] : 0.0,
    //     ];
    //     $this->sendNotification($activity_data);

    //      if($result->postServiceMapping()->count() > 0)
    //     {
    //         $result->postServiceMapping()->delete();
    //     }
    //     if ($request->has('service_id')) {
    //         $service_id = explode(',',$request->service_id);
    //         if (is_array($service_id)) {
    //             foreach ($service_id as $service) {
    //                 $post_services = [
    //                     'post_request_id' => $result->id,
    //                     'service_id' => $service,
    //                 ];
    //                 $result->postServiceMapping()->insert($post_services);
    //             }
    //         }
    //     }
    //     if($request->status == 'assigned'){
    //         $activity_data = [
    //             'activity_type' => 'user_accept_bid',
    //             'post_job_id' => $result->id,
    //             'post_job' => $result,
    //             'latitude' =>isset($data['latitude']) ? $data['latitude'] : 0.0,
    //             'longitude' => isset($data['longitude']) ? $data['longitude'] : 0.0,
    //         ];
    //         $this->sendNotification($activity_data);

    //     }
    //     $message = __('messages.update_form',[ 'form' => __('messages.postrequest') ] );
	// 	if($result->wasRecentlyCreated){
	// 		$message = __('messages.save_form',[ 'form' => __('messages.postrequest') ] );
	// 	}

    //     if($request->is('api/*')) {
    //         return comman_message_response($message);
	// 	}

	// 	return redirect(route('service.index'))->withSuccess($message);
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
       
            Log::info($data);
            $bidData = '';
            if(isset($data['post_request_id'])){
                $postRequestId = $data['post_request_id'];
                $bidData = PostJobBid::where('id',$postRequestId)->first();
                // dd($bidData);
                $data['job_price'] = $bidData->price;
                $data['title'] = $bidData->title;
                $data['duration'] = $bidData->duration;
                $data['provider_id'] = $bidData->provider_id;
            }
            // dd($data['total_days']);
            // if (array_key_exists('total_days', $data)) {
            //     $data['total_hours'] = $data['total_days'];
            //     unset($data['total_days']);
            // }

        // if(isset( $data['start_date']) && isset( $data['end_date']))
        // {
        //     $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        //     $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
        // }

        // if (array_key_exists('total_houres', $data)) {
            //     $data['total_hours'] = $data['total_houres'];
        //     unset($data['total_houres']);
        // }

        // Remove subcategory_id key if its value is null
        if ($request->subcategory_id == null) {
            unset($data['subcategory_id']);
        }
        if ($request->startDate) {
            $data['start_date']= $request->startDate ;
        }
        $data['type']= $request->type ?? NULL;
        $data['status']= $request->status ?? 'requested';
        $data['customer_id'] =  !empty($request->customer_id) ? $request->customer_id : auth()->user()->id;
        $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);
        if($bidData != ''){
            $updateBidData = PostJobBid::where(['post_request_id'=>$bidData->post_request_id,'customer_id'=>$bidData->customer_id])->update(['status'=>0]);    // update status b0 after accept the BID
        }

        if ($request->is('api/*')) {
            if ($request->has('attachment_count')) {
                if($request->has('attchments')){
                    $files = $request->attchments;
                    storeMediaFile($result, $files, 'post_job_request');
                };

            }
        } else {
            storeMediaFile($result, $request->image, 'post_job_request');
        };

        $activity_data = [
            'activity_type' => 'job_requested',
            'post_job_id' => $result->id,
            'post_job' => $result,
        ];

        // saveRequestJobActivity($activity_data);
        if($result->postServiceMapping()->count() > 0)
        {
            $result->postServiceMapping()->delete();
        }

        if($request->service_id != null) {
            foreach($request->service_id as $service) {
                $post_services = [
                    'post_request_id'   => $result->id,
                    'service_id'   => $service,
                ];
                $result->postServiceMapping()->insert($post_services);
            }
        }
        if($request->status == 'accept'){
            $activity_data = [
                'activity_type' => 'user_accept_bid',
                'post_job_id' => $result->id,
                'post_job' => $result,
            ];

            // saveRequestJobActivity($activity_data);
        }
        $message = __('messages.update_form',[ 'form' => __('messages.postrequest') ] );
        if($result->wasRecentlyCreated){
            $message = __('messages.save_form',[ 'form' => __('messages.postrequest') ] );
        }

        if($request->is('api/*')) {
            return comman_message_response($message);
        }

        return redirect(route('post-job-request.index'))->withSuccess($message);
    }

public function getpostjob(Request $request){
    $data = $request->all();
    $data['status']= $request->status ?? 'requested';
    $data['job_price']= $request->job_price;
    $data['provider_id']= $request->provider_id;
    $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);
  return redirect()->back();
}

public function show($id)
{
   
    $pageTitle = trans('messages.list_form_title',['form' => trans('messages.postbid')] );
    $auth_user = authSession();
    $asset = ['datatable'];
    return view('postrequest.view', compact('pageTitle', 'auth_user', 'asset','id'));
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
        $postJob = PostJobRequest::with('category','country','subCategory','city')->find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.post_job')]);
        if($postJob){
            return view('postrequest.edit',compact('postJob','pageTitle'));
        }
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

        public function filterPostJobRequests(Request $request)
        {
            if(auth()->check()){
                $query = PostJobRequest::myPostJob()->whereIn('status',['requested','accepted','assigned']);
            }else{
                $query = PostJobRequest::query();
            }
            // dd($query);
            $list_data_limit = null;
            if ($request->has('search') && $request->search != null) {
                    $query->where('title','like',"%{$request->search}%")
                    ->orWhere('description','like',"%{$request->search}%")
                    ->orWhere('price','like',"%{$request->search}%")
                    ->orWhere('job_price','like',"%{$request->search}%")
                    ->orWhere('reason','like',"%{$request->search}%");
            }
            if ($request->has('category_id') && $request->category_id != null) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('subcategory_id') && $request->subcategory_id != null) {
                $query->where('subcategory_id', $request->subcategory_id);
            }
            if ($request->has('provider_id') && $request->provider_id != null) {
                $query->where('provider_id', $request->provider_id);
            }
            if ($request->has('country_id') && $request->country_id != null) {
                $query->where('country_id', $request->country_id);
            }
            if ($request->has('city_id') && $request->city_id != null) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->has('lower') && $request->has('upper')&& $request->lower != null && $request->upper != null) {
                $query->whereBetween('price', [$request->lower, $request->upper]);
            }

            if ($request->has('sort_by') && $request->sort_by != null) {
                $sort_by = $request->sort_by;
                switch ($sort_by) {
                    case 'automatic':
                        $query->orderBy('id','desc');
                        break;
                    case 'lowest_price':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'highest_price':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'latest_service':
                        $query->orderBy('updated_at', 'desc');
                        break;
                    default:
                        $query->orderBy('id','desc');
                        break;
                }
            }  else{
                $query = $query->orderBy('created_at','desc');
            }


            $filteredData = $query->paginate(6);
            // dd($filteredData);

            $html = view('landing-page.post_job_price_filter', compact('filteredData'))->render();

            // return response()->json(['status' => true, 'html' => $html]);
            return response()->json([
                'status' => true,
                'html' => $html,
                'pagination' => $filteredData->links()->toHtml(),
            ]);


        }

}
