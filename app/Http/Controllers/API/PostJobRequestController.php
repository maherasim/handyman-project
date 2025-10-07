<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostRequestStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

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
            $postjob->cancel_bid_id = $bid->id; // reset if cancelled
           
            $postjob->provider_id = $bid->provider_id;

        } elseif ($bid->status === 'accepted') {
            $postjob->status = 'accepted';
            $postjob->accepted_bid_id = $bid->id; // ✅ store accepted bid id
            $postjob->provider_id = $bid->provider_id;
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
    public function editPostJob($id)
    {
        $auth_user = auth()->user();
        $postJob = PostJobRequest::find($id);
    
        if (!$postJob) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post Job not found'
            ], 404);
        }
    
        // Allow admin or the owner to edit
        if (!$auth_user->hasAnyRole(['admin']) && $auth_user->id !== $postJob->customer_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to edit this job'
            ], 403);
        }
    
        // Convert stored image paths into full URLs
        $imageUrls = [];
        if (!empty($postJob->images)) {
            foreach ($postJob->images as $img) {
                $imageUrls[] = asset('storage/' . $img);
            }
        }
    
        // Replace images field with full URLs
        $postJob->images = $imageUrls;
    
        // If you also have single image field
        if (!empty($postJob->image)) {
            $postJob->image = asset('storage/' . $postJob->image);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'postJob' => $postJob,
                'auth_user' => $auth_user,
                'pageTitle' => __('messages.update_form_title', ['form' => __('messages.post_job')])
            ]
        ], 200);
    }
    
    
    

    

    public function invoice($id)
    {
        $bid = \App\Models\PostJobBid::with([
            'provider:id,display_name,address,vat_number',
            'customer:id,display_name,address',
            'postrequest:id,title,price_type,job_price,total_days,total_hours,country_id,city_id',
            'postrequest.city:id,name',
            'postrequest.country:id,name',
            'extraCharges',
        ])->findOrFail($id);
        
        // Authorize: Only the customer or provider associated with the bid may request the invoice
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $authUser = auth()->user();
        $isCustomer = (int) $authUser->id === (int) $bid->customer_id;
        $isProvider = (int) $authUser->id === (int) $bid->provider_id;
        if (!$isCustomer && !$isProvider) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $payment = \App\Models\PaymentPostJOb::where('post_job_bid_request_id', $bid->id)
            ->latest('id')
            ->first();

        $pdf = Pdf::loadView('postrequest.invoice', compact('bid', 'payment'))->setPaper('a4');
        $filename = 'post-bid-invoice-' . $bid->id . '.pdf';
        $pdfOutput = $pdf->output();

        // Email the invoice PDF to the matched user (customer or provider)
        $recipientId = $isCustomer ? $bid->customer_id : $bid->provider_id;
        $recipient = User::find($recipientId);
        if (!$recipient || empty($recipient->email)) {
            return response()->json(['status' => false, 'message' => 'Recipient email not found'], 422);
        }

        try {
            $subject = 'Your Invoice for Post Job Bid #' . $bid->id;
            $body = 'Hello ' . (trim((string)($recipient->display_name ?? $recipient->name ?? '')) ?: 'there') . ",\n\nPlease find your invoice attached.\n\nThank you.";
            Mail::raw($body, function ($message) use ($recipient, $subject, $filename, $pdfOutput) {
                $message->to($recipient->email, $recipient->display_name ?? $recipient->name ?? null)
                    ->subject($subject)
                    ->attachData($pdfOutput, $filename, ['mime' => 'application/pdf']);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Failed to send email'], 500);
        }

        return response()->json(['status' => true, 'message' => 'Email sent successfully']);
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
        $query = PostJobRequest::withCount('postBidList')->whereIn('status', [
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
    public function getPostRequestDetail(Request $request)
    {
        $id = $request->post_request_id;
        $user = auth()->user();
    
        // ✅ Base query
        $query = PostJobRequest::query();
    
        // 🔐 Restrict based on role
        if ($user->hasRole('user')) {
            // Users can only view their own posts
            $query->where('customer_id', $user->id);
        }
        // Admins and Providers can view any post — no extra filter
    
        // 🔍 Find the post
        $post_request = $query->find($id);
    
        // ❌ Not found or unauthorized
        if (empty($post_request)) {
            $message = __('messages.record_not_found');
            return comman_message_response($message, 400);
        }
    
        // ✅ Increment view count
        try {
            $post_request->increment('total_views');
        } catch (\Throwable $e) {
            // Optional: log the exception
        }
    
        // ✅ Load detail & bids
        $post_request_detail = new PostJobRequestDetailResource($post_request);
        $bider_data = PostJobBiderResource::collection(
            PostJobBid::where('post_request_id', $id)->get()
        );
    
        // ✅ Return response
        return comman_custom_response([
            'post_request_detail' => $post_request_detail,
            'bider_data' => $bider_data,
        ]);
    }
       
 
}