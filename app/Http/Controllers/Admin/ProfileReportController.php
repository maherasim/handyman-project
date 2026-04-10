<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileReportController extends Controller
{
    public function index()
    {
        $reports = ProfileReport::with(['reporter', 'reportedUser', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.profile-reports.index', compact('reports'));
    }

    public function show(ProfileReport $profileReport)
    {
        $profileReport->load(['reporter', 'reportedUser', 'reviewer']);

        return view('admin.profile-reports.show', compact('profileReport'));
    }

    public function update(Request $request, ProfileReport $profileReport)
    {
        $request->validate([
            'action' => 'required|in:dismiss,deactivate_user,activate_user',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $admin = Auth::user();
        if (! $admin || ! $admin->hasAnyRole(['admin', 'demo_admin'])) {
            abort(403);
        }

        $reportedUser = $profileReport->reportedUser;
        $note = $request->input('admin_note');
        $action = $request->input('action');

        if ($action === 'deactivate_user' && $reportedUser) {
            $reportedUser->update(['status' => 0]);
        } elseif ($action === 'activate_user' && $reportedUser) {
            $reportedUser->update(['status' => 1]);
        }

        $profileReport->update([
            'status' => $action === 'dismiss' ? 'dismissed' : 'action_taken',
            'admin_note' => $note,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.profile-reports.index')->with('success', 'Profile report updated successfully.');
    }
}
