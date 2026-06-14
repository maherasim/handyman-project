<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PostJobRequest;

class SocialShareController extends Controller
{
    public function postJobToFacebook(Request $request, int $id)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')), 403);

        if (!config('services.facebook.page_id') || !config('services.facebook.access_token')) {
            return response()->json(['message' => __('messages.facebook_not_configured')], 422);
        }

        $job = PostJobRequest::with(['city', 'country', 'customer'])
            ->find($id);

        if (!$job) {
            return response()->json(['message' => __('messages.job_not_found')], 404);
        }

        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.access_token');

        $imageUrl = null;
        if (!empty($job->image)) {
            $imageUrl = asset('storage/' . ltrim($job->image, '/'));
        }

        $jobUrl = route('job.details', $job->id);

        $priceType = $job->price_type ?: 'fixed';
        $locationText = trim(($job->city->name ?? 'City') . ', ' . ($job->country->name ?? 'Country'));

        $caption = $job->title . "\n"
            . 'Price: ' . (function ($price) { return is_null($price) ? '-' : ('€' . number_format($price)); })($job->price)
            . ' / ' . ucfirst($priceType) . "\n"
            . 'Location: ' . $locationText . "\n\n"
            . 'View details: ' . $jobUrl;

        try {
            if ($imageUrl) {
                $response = Http::asForm()
                    ->post("https://graph.facebook.com/v19.0/{$pageId}/photos", [
                        'url' => $imageUrl,
                        'caption' => $caption,
                        'access_token' => $accessToken,
                    ]);
            } else {
                $response = Http::asForm()
                    ->post("https://graph.facebook.com/v19.0/{$pageId}/feed", [
                        'message' => $caption,
                        'access_token' => $accessToken,
                    ]);
            }

            if (!$response->successful()) {
                Log::warning('Facebook post failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'message' => __('messages.facebook_publish_failed'),
                    'details' => $response->json(),
                ], 422);
            }

            return response()->json([
                'message' => __('messages.published_to_facebook'),
                'result' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Facebook post exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => __('messages.facebook_publish_error')], 500);
        }
    }
}


