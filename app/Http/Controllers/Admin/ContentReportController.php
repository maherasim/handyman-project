<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRating;
use App\Models\ContentReport;
use App\Models\PostJobRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentReportController extends Controller
{
    public function index()
    {
        $reports = ContentReport::with(['reporter', 'subjectUser', 'reviewer', 'reportable'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.content-reports.index', compact('reports'));
    }

    public function show(ContentReport $contentReport)
    {
        $contentReport->load(['reporter', 'subjectUser', 'reviewer', 'reportable']);

        return view('admin.content-reports.show', compact('contentReport'));
    }

    public function update(Request $request, ContentReport $contentReport)
    {
        $request->validate([
            'action' => 'required|in:dismiss,hide_service,restore_service,hide_post_job,restore_post_job,hide_review,restore_review',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $admin = Auth::user();
        if (! $admin || ! $admin->hasAnyRole(['admin', 'demo_admin'])) {
            abort(403);
        }

        $note = $request->input('admin_note');
        $service = $contentReport->reportable_type === Service::class
            ? Service::find($contentReport->reportable_id)
            : null;
        $postJob = $contentReport->reportable_type === PostJobRequest::class
            ? PostJobRequest::find($contentReport->reportable_id)
            : null;
        $review = $contentReport->reportable_type === BookingRating::class
            ? BookingRating::withTrashed()->find($contentReport->reportable_id)
            : null;

        switch ($request->input('action')) {
            case 'dismiss':
                $contentReport->update([
                    'status' => 'dismissed',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'hide_service':
                if ($service) {
                    $service->update(['is_hidden_from_public' => true]);
                }
                $contentReport->update([
                    'status' => 'action_taken',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'restore_service':
                if ($service) {
                    $service->update(['is_hidden_from_public' => false]);
                }
                $contentReport->update([
                    'status' => 'reviewed',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'hide_post_job':
                if ($postJob) {
                    $postJob->update(['is_hidden_from_public' => true]);
                }
                $contentReport->update([
                    'status' => 'action_taken',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'restore_post_job':
                if ($postJob) {
                    $postJob->update(['is_hidden_from_public' => false]);
                }
                $contentReport->update([
                    'status' => 'reviewed',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'hide_review':
                if ($review) {
                    if ($review->trashed()) {
                        $review->restore();
                    }
                    $review->update(['status' => BookingRating::STATUS_HIDDEN]);
                }
                $contentReport->update([
                    'status' => 'action_taken',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
            case 'restore_review':
                if ($review) {
                    if ($review->trashed()) {
                        $review->restore();
                    }
                    $review->update(['status' => BookingRating::STATUS_VISIBLE]);
                }
                $contentReport->update([
                    'status' => 'reviewed',
                    'admin_note' => $note,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);
                break;
        }

        return redirect()->route('admin.content-reports.index')->with('success', __('messages.ugc_admin_updated'));
    }
}
