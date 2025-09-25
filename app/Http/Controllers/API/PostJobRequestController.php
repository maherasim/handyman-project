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

        // Update postjob status
        if ($bid->status === 'cancelled') {
            $postjob->status = 'requested'; // special case
        } else {
            $postjob->status = $bid->status;
        }
        $postjob->save();

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
    public function startWork(Request $request, $id)
    {
        // Find the bid
        $post = PostJobBid::findOrFail($id);

        // Ensure user is a provider



        // Update advance, remaining, and status
        $post->advance_percent = $request->input('advance_percent');
        $post->remaining_percent = $request->input('remaining_percent');
        $post->status = 'Advance Payment Pending';
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
    public function showBidById($bidId) 
    {
        $bid = PostJobBid::with([
            'provider:id,display_name',
            'customer:id,display_name',
            'postrequest:id,title,customer_id,status,provider_id,remaining_percent,type,start_date,end_date,total_budget,city_id,country_id,job_price,street_address,house_number,working_address,total_hours,price_type,total_days,accepted_bid_id',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'postrequest.postBidList:id,post_request_id',
            'extraCharges',
        ])->findOrFail($bidId);
    
        // ✅ Get country_id from related PostJobRequest
        $countryId = optional($bid->postrequest)->country_id;
    
        // ✅ Fetch tax percent
        $tax = $countryId ? \App\Models\Tax::where('id', $countryId)->first() : null;
        $tax_percent = $tax ? $tax->value . '%' : null;
    
        return response()->json([
            'success' => true,
            'data' => $bid,
            'tax_percent' => $tax_percent, // ✅ Added tax percent
        ]);
    }
    
    
    public function getPostRequestList(Request $request)
    {
        // Build query without myPostJob() scope
        $query = PostJobRequest::whereIn('status', [
            'requested', 'accepted', 'assigned', 'completed', 'confirm_done',
            'remaining_paid', 'done', 'in_progress', 'in_process', 'hold',
            'advance_paid', 'cancelled', 'pending'
        ]);
    
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