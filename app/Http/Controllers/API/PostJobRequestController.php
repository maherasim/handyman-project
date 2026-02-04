<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\NotificationTrait;
use Illuminate\Http\Request;
use App\Models\PostRequestStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

use App\Models\PostJobRequest;
use App\Models\PostJobBid;
use App\Models\PostJobBidRating;
use App\Models\PostJobBidCustomerRating;
use App\Models\PaymentPostJOb;
use App\Http\Resources\API\PostJobRequestResource;
use App\Http\Resources\API\PostJobBiderResource;
use App\Http\Resources\API\PostJobRequestDetailResource;

class PostJobRequestController extends Controller
{
    use NotificationTrait;
  
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
            $postjob->cancel_bid_id = $bid->id; // which bid was cancelled
            $postjob->accepted_bid_id = null; // clear accepted bid
            $postjob->provider_id = $bid->provider_id;
        } elseif ($bid->status === 'accepted') {
            $postjob->status = 'accepted';
            $postjob->accepted_bid_id = $bid->id; // ✅ store accepted bid id
            $postjob->provider_id = $bid->provider_id;
        } else {
            $postjob->status = $bid->status;
        }
        $postjob->save();

        // Notify the OTHER party (same as web): provider updated → email + notification to customer; customer updated → email + notification to provider
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
        $bid = PostJobBid::findOrFail($id);

        // Update advance, remaining, and status
        $bid->advance_percent = $request->input('advance_percent');
        $bid->remaining_percent = $request->input('remaining_percent');
        $bid->status = 'Advance Payment Pending';
        $bid->save();

        // Notify customer (same as web): email + notification when provider sets payment split
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

        // ✅ Payment summary for bank transfer (from payment_post_jobs)
        $payment = PaymentPostJOb::where('post_job_bid_request_id', $bid->id)
            ->orderBy('id', 'desc')
            ->first();
        $bankTransfer = null;
        if ($payment && $payment->payment_type === 'bank_transfer') {
            $statusLabel = strtolower((string) $payment->payment_status);
            $statusCode = ($statusLabel === 'paid' || $statusLabel === 'approved' || $statusLabel === 'confirmed') ? 1 : 0;
            $bankTransfer = [
                'is_bank_transfer' => 1,
                'status_code' => $statusCode,        // 1=confirmed/paid, 0=pending
                'status' => $payment->payment_status,
                'txn_id' => $payment->txn_id,
                'amount' => (float) $payment->total_amount,
            ];
        }

        // provider_rating_exists: from post_job_bid_ratings = customer has already rated the provider for this bid
        $providerRatingExists = PostJobBidRating::where('post_job_bid_id', $bid->id)->exists();

        // show_rate_customer_button: provider has NOT yet rated the customer (from post_job_bid_customer_ratings)
        $providerHasRatedCustomer = PostJobBidCustomerRating::where('post_job_bid_id', $bid->id)
            ->where('provider_id', $bid->provider_id)
            ->exists();
        $canProviderRate = in_array(strtolower((string)($bid->status ?? '')), ['remaining_paid', 'completed']);
        $showRateCustomerButton = $canProviderRate && !$providerHasRatedCustomer;

        // Only use street_address and house_number; do not send working_address
        if ($bid->postrequest) {
            $bid->postrequest->makeHidden(['working_address']);
        }

        return response()->json([
            'success' => true,
            'data' => $bid,
            'tax_percent' => $tax_percent,
            'bank_transfer' => $bankTransfer,
            'provider_rating_exists' => $providerRatingExists,
            'show_rate_customer_button' => $showRateCustomerButton,
        ]);
    }
    
    
    public function getPostRequestList(Request $request)
    {
        // Apply role-aware scope: users see only their own posts; admins/providers see all
        $query = PostJobRequest::withCount('postBidList')->myPostJob()->whereIn('status', [
            'requested', 'accepted', 'assigned', 'completed', 'confirm_done',
            'remaining_paid', 'done', 'in_progress', 'in_process', 'hold',
            'advance_paid', 'cancelled', 'pending'
        ]);
        
    
        // Default per page from config; ensure integer fallback
        $per_page = (int) (config('constant.PER_PAGE_LIMIT') ?? 10);

		// Always show latest posts first
    
        // Sanitize per_page
        $perPageParam = $request->input('per_page');
        if (is_numeric($perPageParam)) {
            $per_page = max(1, (int) $perPageParam);
        } elseif (strtolower((string) $perPageParam) === 'all') {
            $per_page = max(1, (int) $query->count());
        }
    
		// Paginate (latest by creation date first; id as tiebreaker)
		$paginator = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($per_page);
    
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

        // show_update_bid = true when provider can bid: no bid yet, or their bid status is "requested"; otherwise false
        $show_update_bid = false;
        if ($user->hasRole('provider')) {
            $providerBid = PostJobBid::where('post_request_id', $id)
                ->where('provider_id', $user->id)
                ->orderByDesc('id')
                ->first();
            if (!$providerBid) {
                $show_update_bid = true; // no bid yet → can place bid
            } elseif (strtolower((string)($providerBid->status ?? '')) === 'requested') {
                $show_update_bid = true; // bid is requested → can update bid
            }
        }

        return comman_custom_response([
            'post_request_detail' => $post_request_detail,
            'bider_data' => $bider_data,
            'show_update_bid' => $show_update_bid,
        ]);
    }
       
 
}