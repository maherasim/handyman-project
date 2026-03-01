<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PostJobBid;
use App\Models\PostJobBidRating;
use App\Models\PostJobBidCustomerRating;
use App\Notifications\CommonNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostJobBidRatingController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'post_job_bid_id' => 'required|integer|exists:post_job_bids,id',
            'provider_id' => 'required|integer|exists:users,id',
            'customer_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:5000',
        ]);

        $bid = PostJobBid::findOrFail((int) $request->post_job_bid_id);
        $userId = (int) Auth::id();
        // Only the customer of this bid may rate the provider
        abort_unless($userId && $userId === (int) ($bid->customer_id ?? 0), 403);

        // Customer rates provider → post_job_bid_customer_ratings
        $rating = PostJobBidCustomerRating::updateOrCreate(
            [
                'post_job_bid_id' => (int) $request->post_job_bid_id,
                'customer_id' => $userId,
            ],
            [
                'provider_id' => (int) $request->provider_id,
                'rating' => (int) $request->rating,
                'review' => (string) ($request->review ?? ''),
            ]
        );

        // Notify provider (employer) – in-app + email
        try {
            $bid = PostJobBid::with(['postrequest', 'customer', 'provider'])->findOrFail((int) $request->post_job_bid_id);
            $provider = $bid->provider;
            if ($provider && $bid->postrequest) {
                $jobId = $bid->post_request_id;
                $jobName = $bid->postrequest->title ?? __('Job Request');
                $link = route('post-job-bid.show', ['id' => $jobId]);
                $provider->notify(new CommonNotification('post_job_bid_rated_provider', [
                    'user_type' => 'provider',
                    'job_id' => $jobId,
                    'job_name' => $jobName,
                    'customer_name' => $bid->customer ? $bid->customer->display_name : '',
                    'provider_name' => $provider->display_name ?? '',
                    'rating' => (string) $request->rating,
                    'link' => $link,
                    'company_name' => config('app.name', 'Frobster'),
                ]));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('post_job_bid_rated_provider notification failed: ' . $e->getMessage());
        }

        return response()->json(['status' => true, 'id' => $rating->id]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:post_job_bid_customer_ratings,id',
        ]);
        $rating = PostJobBidCustomerRating::findOrFail((int) $request->id);
        abort_unless((int) Auth::id() === (int) $rating->customer_id, 403);
        $rating->delete();
        return response()->json(['status' => true]);
    }

    /**
     * Provider rates customer (post-job bid).
     * POST postbid/rating-by-provider/save
     */
    public function saveByProvider(Request $request)
    {
        $request->validate([
            'post_job_bid_id' => 'required|integer|exists:post_job_bids,id',
            'provider_id' => 'required|integer|exists:users,id',
            'customer_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:5000',
        ]);

        $bid = PostJobBid::findOrFail((int) $request->post_job_bid_id);
        $userId = (int) Auth::id();
        abort_unless($userId && $userId === (int) ($bid->provider_id ?? 0), 403);

        $rating = PostJobBidCustomerRating::updateOrCreate(
            [
                'post_job_bid_id' => (int) $request->post_job_bid_id,
                'provider_id' => $userId,
            ],
            [
                'customer_id' => (int) $request->customer_id,
                'rating' => (int) $request->rating,
                'review' => (string) ($request->review ?? ''),
            ]
        );

        // Notify customer – in-app + email
        try {
            $bid = PostJobBid::with(['postrequest', 'customer', 'provider'])->findOrFail((int) $request->post_job_bid_id);
            $customer = $bid->customer;
            if ($customer && $bid->postrequest) {
                $jobId = $bid->post_request_id;
                $jobName = $bid->postrequest->title ?? __('Job Request');
                $link = route('post-job-bid.show', ['id' => $jobId]);
                $customer->notify(new CommonNotification('post_job_bid_rated_customer', [
                    'user_type' => 'user',
                    'job_id' => $jobId,
                    'job_name' => $jobName,
                    'provider_name' => $bid->provider ? $bid->provider->display_name : '',
                    'customer_name' => $customer->display_name ?? '',
                    'rating' => (string) $request->rating,
                    'link' => $link,
                    'company_name' => config('app.name', 'Frobster'),
                ]));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('post_job_bid_rated_customer notification failed: ' . $e->getMessage());
        }

        return response()->json(['status' => true, 'id' => $rating->id]);
    }

    /**
     * Provider deletes their rating of the customer.
     * POST postbid/rating-by-provider/delete (stored in post_job_bid_ratings)
     */
    public function deleteByProvider(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:post_job_bid_ratings,id',
        ]);
        $rating = PostJobBidRating::findOrFail((int) $request->id);
        abort_unless((int) Auth::id() === (int) $rating->provider_id, 403);
        $rating->delete();
        return response()->json(['status' => true]);
    }
}


