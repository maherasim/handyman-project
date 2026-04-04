<?php

namespace App\Http\Controllers;

use App\Models\ContentReport;
use App\Models\PostJobRequest;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBlock;
use App\Support\UgcListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UgcSafetyController extends Controller
{
    public function reportContent(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::isCustomer($user)) {
            return response()->json(['message' => __('messages.ugc_login_as_customer')], 403);
        }

        $v = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:services,id',
            'reason' => 'required|string|in:spam,harassment,inappropriate,fraud,other',
            'details' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $service = Service::find($request->integer('service_id'));
        if (! $service || (int) $service->status !== 1) {
            return response()->json(['message' => __('messages.ugc_service_not_found')], 404);
        }

        if ((int) $service->provider_id === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_report_own')], 422);
        }

        $existing = ContentReport::query()
            ->where('reporter_id', $user->id)
            ->where('reportable_type', Service::class)
            ->where('reportable_id', $service->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['message' => __('messages.ugc_already_reported')], 422);
        }

        ContentReport::create([
            'reporter_id' => $user->id,
            'reportable_type' => Service::class,
            'reportable_id' => $service->id,
            'subject_user_id' => $service->provider_id,
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        $pending = UgcListing::pendingReportsCountForService($service->id);
        if ($pending >= 3) {
            $service->update(['is_hidden_from_public' => true]);
        }

        $this->notifyAdminNewReport($service, $user);

        return response()->json([
            'message' => __('messages.ugc_report_received'),
            'policy' => __('messages.ugc_policy_24h'),
        ]);
    }

    public function reportPostJob(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::canLoadUgcScripts($user)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'post_job_id' => 'required|integer|exists:post_job_requests,id',
            'reason' => 'required|string|in:spam,harassment,inappropriate,fraud,other',
            'details' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $job = PostJobRequest::find($request->integer('post_job_id'));
        if (! $job) {
            return response()->json(['message' => __('messages.ugc_job_not_found')], 404);
        }

        if ((int) $job->customer_id === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_report_own')], 422);
        }

        if (! UgcListing::canReportPostJob($user, $job)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $existing = ContentReport::query()
            ->where('reporter_id', $user->id)
            ->where('reportable_type', PostJobRequest::class)
            ->where('reportable_id', $job->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['message' => __('messages.ugc_already_reported')], 422);
        }

        ContentReport::create([
            'reporter_id' => $user->id,
            'reportable_type' => PostJobRequest::class,
            'reportable_id' => $job->id,
            'subject_user_id' => $job->customer_id,
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        $pending = UgcListing::pendingReportsCountForPostJob($job->id);
        if ($pending >= 3) {
            $job->update(['is_hidden_from_public' => true]);
        }

        $this->notifyAdminNewPostJobReport($job, $user);

        return response()->json([
            'message' => __('messages.ugc_report_received'),
            'policy' => __('messages.ugc_policy_24h'),
        ]);
    }

    public function blockUser(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::canLoadUgcScripts($user)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'blocked_user_id' => 'required|integer|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $blockedId = $request->integer('blocked_user_id');
        if ($blockedId === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_block_self')], 422);
        }

        $blocked = User::find($blockedId);
        if (! $blocked || ! in_array($blocked->user_type, ['provider', 'user', 'handyman'], true)) {
            return response()->json(['message' => __('messages.ugc_block_user_target_only')], 422);
        }

        UserBlock::firstOrCreate(
            ['blocker_id' => $user->id, 'blocked_id' => $blockedId],
            []
        );

        return response()->json(['message' => __('messages.ugc_user_blocked')]);
    }

    public function unblockUser(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::canLoadUgcScripts($user)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'blocked_user_id' => 'required|integer|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $request->integer('blocked_user_id'))
            ->delete();

        return response()->json(['message' => __('messages.ugc_user_unblocked')]);
    }

    protected function notifyAdminNewReport(Service $service, User $reporter): void
    {
        try {
            $email = null;
            $general = \App\Models\Setting::where('type', 'general-setting')->where('key', 'general-setting')->first();
            if ($general && $general->value) {
                $decoded = json_decode($general->value);
                $email = $decoded->inquriy_email ?? $decoded->inquiry_email ?? null;
            }
            if (empty($email) && config('mail.from.address')) {
                $email = config('mail.from.address');
            }
            if (empty($email)) {
                return;
            }

            Mail::raw(
                __('messages.ugc_admin_email_body', [
                    'service' => $service->name,
                    'service_id' => $service->id,
                    'reporter' => $reporter->email,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($email) {
                    $message->to($email)->subject(__('messages.ugc_admin_email_subject'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyAdminNewPostJobReport(PostJobRequest $job, User $reporter): void
    {
        try {
            $email = null;
            $general = \App\Models\Setting::where('type', 'general-setting')->where('key', 'general-setting')->first();
            if ($general && $general->value) {
                $decoded = json_decode($general->value);
                $email = $decoded->inquriy_email ?? $decoded->inquiry_email ?? null;
            }
            if (empty($email) && config('mail.from.address')) {
                $email = config('mail.from.address');
            }
            if (empty($email)) {
                return;
            }

            Mail::raw(
                __('messages.ugc_admin_email_body_post_job', [
                    'title' => $job->title,
                    'post_job_id' => $job->id,
                    'reporter' => $reporter->email,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($email) {
                    $message->to($email)->subject(__('messages.ugc_admin_email_subject_post_job'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
