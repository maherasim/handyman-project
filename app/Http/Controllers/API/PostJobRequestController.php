<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostRequestStatus;
use App\Models\PostJobRequest;
use App\Models\PostJobBid;
use App\Http\Resources\API\PostJobRequestResource;
use App\Http\Resources\API\PostJobBiderResource;
use App\Http\Resources\API\PostJobRequestDetailResource;

class PostJobRequestController extends Controller
{
  
    public function postRequestStatus(Request $request)
    {
        $post_job_status = PostRequestStatus::orderBy('sequence')->get();
        return comman_custom_response($post_job_status);
    }
    public function getPostRequestList(Request $request)
    {
        $user = auth()->user();
    
        $query = PostJobRequest::myPostJob()
            ->whereIn('status', ['requested', 'accepted', 'assigned']);
    
        // Default per page from config; ensure integer fallback
        $per_page = (int) (config('constant.PER_PAGE_LIMIT') ?? 10);
    
        // Sanitize order direction
        $orderBy = strtolower((string) $request->input('orderby', 'desc'));
        $orderBy = in_array($orderBy, ['asc', 'desc'], true) ? $orderBy : 'desc';
    
        // Sanitize per_page
        $perPageParam = $request->input('per_page');
        if (is_numeric($perPageParam)) {
            $per_page = max(1, (int) $perPageParam);
        } elseif (strtolower((string) $perPageParam) === 'all') {
            $per_page = max(1, (int) $query->count());
        }
    
        // Paginate
        $paginator = $query->orderBy('id', $orderBy)->paginate($per_page);
    
        // Wrap items with resource
        $items = PostJobRequestResource::collection($paginator);
    
        $response = [
            'pagination' => [
                'total_items'   => $paginator->total(),
                'per_page'      => $paginator->perPage(),
                'currentPage'   => $paginator->currentPage(),
                'totalPages'    => $paginator->lastPage(),
                'from'          => $paginator->firstItem(),
                'to'            => $paginator->lastItem(),
                'next_page'     => $paginator->nextPageUrl(),
                'previous_page' => $paginator->previousPageUrl(),
            ],
            'data' => $items,
        ];
    
        return comman_custom_response($response);
    }
    public function getPostRequestDetail(Request $request){
        $id = $request->post_request_id;
        $post_request = PostJobRequest::myPostJob()->find($id);
        if(empty($post_request)){
            $message = __('messages.record_not_found');
            return comman_message_response($message,400);   
        }
        // increment total views
        try {
            $post_request->increment('total_views');
        } catch (\Throwable $e) {
            // ignore
        }

        $post_request_detail = new PostJobRequestDetailResource($post_request);
        $bider_data = PostJobBiderResource::collection(PostJobBid::where('post_request_id',$id)->get());
        $response = [
            'post_request_detail'    => $post_request_detail,
            'bider_data'    => $bider_data,
        ];

        return comman_custom_response($response);
    }
}