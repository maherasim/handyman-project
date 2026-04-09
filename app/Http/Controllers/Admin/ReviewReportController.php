<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRating;
use App\Models\CustomerRating;
use App\Models\PostJobBidCustomerRating;
use App\Models\PostJobBidRating;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewReportController extends Controller
{
    public function index()
    {
        $reports = ReviewReport::with(['reporter', 'reviewOwner', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.review-reports.index', compact('reports'));
    }

    public function update(Request $request, ReviewReport $reviewReport)
    {
        $request->validate([
            'action' => 'required|in:dismiss,hide_review,restore_review',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $admin = Auth::user();
        if (! $admin || ! $admin->hasAnyRole(['admin', 'demo_admin'])) {
            abort(403);
        }

        $note = $request->input('admin_note');
        $action = $request->input('action');

        $review = match ($reviewReport->review_type) {
            'customer_rating' => CustomerRating::withTrashed()->find($reviewReport->review_id),
            'post_job_bid_rating' => PostJobBidRating::query()->find($reviewReport->review_id),
            'post_job_bid_customer_rating' => PostJobBidCustomerRating::query()->find($reviewReport->review_id),
            default => BookingRating::withTrashed()->find($reviewReport->review_id),
        };

        $hiddenStatus = match (true) {
            $review instanceof CustomerRating => CustomerRating::STATUS_HIDDEN,
            $review instanceof PostJobBidRating => PostJobBidRating::STATUS_HIDDEN,
            $review instanceof PostJobBidCustomerRating => PostJobBidCustomerRating::STATUS_HIDDEN,
            $review instanceof BookingRating => BookingRating::STATUS_HIDDEN,
            default => null,
        };
        $visibleStatus = match (true) {
            $review instanceof CustomerRating => CustomerRating::STATUS_VISIBLE,
            $review instanceof PostJobBidRating => PostJobBidRating::STATUS_VISIBLE,
            $review instanceof PostJobBidCustomerRating => PostJobBidCustomerRating::STATUS_VISIBLE,
            $review instanceof BookingRating => BookingRating::STATUS_VISIBLE,
            default => null,
        };

        if ($action === 'hide_review' && $review && $hiddenStatus !== null) {
            if (method_exists($review, 'trashed') && $review->trashed()) {
                $review->restore();
            }
            $review->update(['status' => $hiddenStatus]);
        } elseif ($action === 'restore_review' && $review && $visibleStatus !== null) {
            if (method_exists($review, 'trashed') && $review->trashed()) {
                $review->restore();
            }
            $review->update(['status' => $visibleStatus]);
        }

        $reviewReport->update([
            'status' => $action === 'dismiss' ? 'dismissed' : ($action === 'hide_review' ? 'action_taken' : 'reviewed'),
            'admin_note' => $note,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.review-reports.index')->with('success', 'Review report updated successfully.');
    }
}
