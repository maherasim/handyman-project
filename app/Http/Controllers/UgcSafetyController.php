<?php

namespace App\Http\Controllers;

use App\Models\ContentReport;
use App\Models\BookingRating;
use App\Models\PostJobRequest;
use App\Models\ProfileReport;
use App\Models\CustomerRating;
use App\Models\PostJobBid;
use App\Models\PostJobBidCustomerRating;
use App\Models\PostJobBidRating;
use App\Models\HandymanRating;
use App\Models\ReviewReport;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBlock;
use App\Support\UgcListing;
use App\Support\UgcReportReasons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UgcSafetyController extends Controller
{
    protected function reportReasonOptions(): array
    {
        return UgcReportReasons::options();
    }

    protected function reportReasonValues(): array
    {
        return UgcReportReasons::valueKeys();
    }

    public function reportProfile(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! in_array((string) $user->user_type, ['user', 'provider', 'handyman'], true)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'reported_user_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|in:'.implode(',', $this->reportReasonValues()),
            'details' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $reportedUser = User::find($request->integer('reported_user_id'));
        if (! $reportedUser || ! in_array((string) $reportedUser->user_type, ['user', 'provider', 'handyman'], true)) {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        if ((int) $reportedUser->id === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_report_own')], 422);
        }

        $existing = ProfileReport::query()
            ->where('reporter_id', $user->id)
            ->where('reported_user_id', $reportedUser->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['message' => __('messages.ugc_already_reported')], 422);
        }

        ProfileReport::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUser->id,
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        $this->notifyAdminNewProfileReport($reportedUser, $user, $request->input('reason'), $request->input('details'));
        $this->notifyReportedUserProfileReported($reportedUser, $request->input('reason'), $request->input('details'));

        return response()->json([
            'message' => __('messages.ugc_report_received'),
            'policy' => __('messages.ugc_policy_24h'),
        ]);
    }

    public function reportProvider(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::canLoadUgcScripts($user)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'provider_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|in:'.implode(',', $this->reportReasonValues()),
            'details' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $provider = User::find($request->integer('provider_id'));
        if (! $provider || ($provider->user_type ?? '') !== 'provider') {
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }

        if ((int) $provider->id === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_report_own')], 422);
        }

        $existing = ContentReport::query()
            ->where('reporter_id', $user->id)
            ->where('reportable_type', User::class)
            ->where('reportable_id', $provider->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['message' => __('messages.ugc_already_reported')], 422);
        }

        ContentReport::create([
            'reporter_id' => $user->id,
            'reportable_type' => User::class,
            'reportable_id' => $provider->id,
            'subject_user_id' => $provider->id,
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        $this->notifyAdminNewProviderReport($provider, $user, $request->input('reason'), $request->input('details'));
        $this->notifyProviderAccountReported($provider, $request->input('reason'), $request->input('details'));

        return response()->json([
            'message' => __('messages.ugc_report_received'),
            'policy' => __('messages.ugc_policy_24h'),
        ]);
    }

    public function reportContent(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! UgcListing::canLoadUgcScripts($user)) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:services,id',
            'reason' => 'required|string|in:'.implode(',', $this->reportReasonValues()),
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

        $this->notifyAdminNewReport($service, $user, $request->input('reason'), $request->input('details'));
        $this->notifyProviderServiceReported($service, $request->input('reason'), $request->input('details'));

        return response()->json([
            'message' => __('messages.ugc_report_received'),
            'policy' => __('messages.ugc_policy_24h'),
        ]);
    }

    public function reportReview(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
        }

        $v = Validator::make($request->all(), [
            'review_type' => 'nullable|string|in:booking_rating,customer_rating,post_job_bid_rating,post_job_bid_customer_rating,handyman_rating',
            'review_id' => 'required|integer|min:1',
            'reason' => 'required|string|in:'.implode(',', $this->reportReasonValues()),
            'details' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $reviewType = (string) $request->input('review_type', 'booking_rating');
        $reviewId = $request->integer('review_id');

        switch ($reviewType) {
            case 'customer_rating':
                $review = CustomerRating::find($reviewId);
                $reviewOwnerId = $review ? (int) $review->provider_id : 0;
                break;
            case 'handyman_rating':
                $review = HandymanRating::find($reviewId);
                $reviewOwnerId = $review ? (int) $review->customer_id : 0;
                break;
            case 'post_job_bid_rating':
                $review = PostJobBidRating::find($reviewId);
                $reviewOwnerId = $review ? (int) $review->customer_id : 0;
                break;
            case 'post_job_bid_customer_rating':
                $review = PostJobBidCustomerRating::find($reviewId);
                $reviewOwnerId = $review ? (int) $review->provider_id : 0;
                break;
            default:
                $review = BookingRating::find($reviewId);
                $reviewOwnerId = $review ? (int) $review->customer_id : 0;
                $reviewType = 'booking_rating';
                break;
        }

        if (! $review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        if (in_array($reviewType, ['post_job_bid_rating', 'post_job_bid_customer_rating'], true)) {
            $bid = PostJobBid::find((int) $review->post_job_bid_id);
            if (! $bid || ! in_array((int) $user->id, [(int) $bid->customer_id, (int) $bid->provider_id], true)) {
                return response()->json(['message' => __('messages.ugc_login_for_report')], 403);
            }
        }

        if ($reviewType === 'handyman_rating' && $review instanceof HandymanRating) {
            if ((int) $user->id !== (int) $review->handyman_id) {
                return response()->json(['message' => __('messages.demo_permission_denied')], 403);
            }
        }

        if ($reviewOwnerId === (int) $user->id) {
            return response()->json(['message' => __('messages.ugc_cannot_report_own')], 422);
        }

        $existing = ReviewReport::query()
            ->where('reporter_id', $user->id)
            ->where('review_type', $reviewType)
            ->where('review_id', $review->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return response()->json(['message' => __('messages.ugc_already_reported')], 422);
        }

        ReviewReport::create([
            'reporter_id' => $user->id,
            'review_type' => $reviewType,
            'review_id' => $review->id,
            'review_owner_id' => $reviewOwnerId,
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        $this->notifyAdminNewReviewReport($reviewType, $review->id, $reviewOwnerId, $user, $request->input('reason'), $request->input('details'));
        $this->notifyReviewOwnerReported($reviewOwnerId, $reviewType, $request->input('reason'), $request->input('details'));

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
            'reason' => 'required|string|in:'.implode(',', $this->reportReasonValues()),
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

        $this->notifyAdminNewPostJobReport($job, $user, $request->input('reason'), $request->input('details'));
        $this->notifyJobOwnerPostJobReported($job, $request->input('reason'), $request->input('details'));

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

    /**
     * Admin notification address — read live from Setting > Mail Settings
     * (/setting/mail-setting), same convention used for other admin-facing
     * notifications (bank transfer, bid payments, etc.). Falls back to the
     * general-setting inquiry email if the mail setting is empty.
     */
    protected function resolveAdminNotificationEmail(): ?string
    {
        $email = getAdminMailFromAddress();
        if (empty($email)) {
            $general = \App\Models\Setting::where('type', 'general-setting')->where('key', 'general-setting')->first();
            if ($general && $general->value) {
                $decoded = json_decode($general->value);
                $email = $decoded->inquriy_email ?? $decoded->inquiry_email ?? null;
            }
        }

        return empty($email) ? null : $email;
    }

    protected function notifyAdminNewReport(Service $service, User $reporter, string $reason, ?string $details): void
    {
        try {
            $email = $this->resolveAdminNotificationEmail();
            if (empty($email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsLine = $detailsTrimmed !== ''
                ? "\n".__('messages.ugc_admin_email_post_job_details_line', ['details' => $detailsTrimmed])
                : '';

            Mail::raw(
                __('messages.ugc_admin_email_body', [
                    'service' => $service->name,
                    'service_id' => $service->id,
                    'reporter' => $reporter->email,
                    'reason' => $reasonLabel,
                    'details_line' => $detailsLine,
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

    protected function notifyProviderServiceReported(Service $service, string $reason, ?string $details): void
    {
        try {
            $service->loadMissing('providers');
            $provider = $service->providers;
            if (! $provider || empty($provider->email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsBlock = $detailsTrimmed !== ''
                ? __('messages.ugc_job_owner_email_details_block', ['details' => $detailsTrimmed])."\n\n"
                : '';

            Mail::raw(
                __('messages.ugc_provider_email_body_service_reported', [
                    'service_name' => $service->name,
                    'reason' => $reasonLabel,
                    'details_block' => $detailsBlock,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($provider) {
                    $message->to($provider->email)->subject(__('messages.ugc_provider_email_subject_service_reported'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyAdminNewPostJobReport(PostJobRequest $job, User $reporter, string $reason, ?string $details): void
    {
        try {
            $email = $this->resolveAdminNotificationEmail();
            if (empty($email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsLine = $detailsTrimmed !== ''
                ? "\n".__('messages.ugc_admin_email_post_job_details_line', ['details' => $detailsTrimmed])
                : '';

            Mail::raw(
                __('messages.ugc_admin_email_body_post_job', [
                    'title' => $job->title,
                    'post_job_id' => $job->id,
                    'reporter' => $reporter->email,
                    'reason' => $reasonLabel,
                    'details_line' => $detailsLine,
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

    protected function notifyJobOwnerPostJobReported(PostJobRequest $job, string $reason, ?string $details): void
    {
        try {
            $job->loadMissing('customer');
            $customer = $job->customer;
            if (! $customer || empty($customer->email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsBlock = $detailsTrimmed !== ''
                ? __('messages.ugc_job_owner_email_details_block', ['details' => $detailsTrimmed])."\n\n"
                : '';

            Mail::raw(
                __('messages.ugc_job_owner_email_body_post_job', [
                    'title' => $job->title,
                    'reason' => $reasonLabel,
                    'details_block' => $detailsBlock,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($customer) {
                    $message->to($customer->email)->subject(__('messages.ugc_job_owner_email_subject_post_job'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyAdminNewProfileReport(User $reportedUser, User $reporter, string $reason, ?string $details): void
    {
        try {
            $email = $this->resolveAdminNotificationEmail();
            if (empty($email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsLine = $detailsTrimmed !== ''
                ? "\n".__('messages.ugc_admin_email_post_job_details_line', ['details' => $detailsTrimmed])
                : '';

            Mail::raw(
                __('messages.ugc_admin_email_body_profile', [
                    'reported_name' => $reportedUser->display_name ?? trim(($reportedUser->first_name ?? '').' '.($reportedUser->last_name ?? '')),
                    'reported_email' => $reportedUser->email,
                    'reported_id' => $reportedUser->id,
                    'reporter' => $reporter->email,
                    'reason' => $reasonLabel,
                    'details_line' => $detailsLine,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($email) {
                    $message->to($email)->subject(__('messages.ugc_admin_email_subject_profile'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyReportedUserProfileReported(User $reportedUser, string $reason, ?string $details): void
    {
        try {
            if (empty($reportedUser->email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsBlock = $detailsTrimmed !== ''
                ? __('messages.ugc_job_owner_email_details_block', ['details' => $detailsTrimmed])."\n\n"
                : '';

            Mail::raw(
                __('messages.ugc_reported_user_email_body_profile', [
                    'reason' => $reasonLabel,
                    'details_block' => $detailsBlock,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($reportedUser) {
                    $message->to($reportedUser->email)->subject(__('messages.ugc_reported_user_email_subject_profile'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyAdminNewProviderReport(User $provider, User $reporter, string $reason, ?string $details): void
    {
        try {
            $email = $this->resolveAdminNotificationEmail();
            if (empty($email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsLine = $detailsTrimmed !== ''
                ? "\n".__('messages.ugc_admin_email_post_job_details_line', ['details' => $detailsTrimmed])
                : '';

            Mail::raw(
                __('messages.ugc_admin_email_body_provider', [
                    'provider_name' => $provider->display_name ?? trim(($provider->first_name ?? '').' '.($provider->last_name ?? '')),
                    'provider_email' => $provider->email,
                    'provider_id' => $provider->id,
                    'reporter' => $reporter->email,
                    'reason' => $reasonLabel,
                    'details_line' => $detailsLine,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($email) {
                    $message->to($email)->subject(__('messages.ugc_admin_email_subject_provider'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyProviderAccountReported(User $provider, string $reason, ?string $details): void
    {
        try {
            if (empty($provider->email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsBlock = $detailsTrimmed !== ''
                ? __('messages.ugc_job_owner_email_details_block', ['details' => $detailsTrimmed])."\n\n"
                : '';

            Mail::raw(
                __('messages.ugc_provider_email_body_account_reported', [
                    'reason' => $reasonLabel,
                    'details_block' => $detailsBlock,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($provider) {
                    $message->to($provider->email)->subject(__('messages.ugc_provider_email_subject_account_reported'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyAdminNewReviewReport(string $reviewType, int $reviewId, int $reviewOwnerId, User $reporter, string $reason, ?string $details): void
    {
        try {
            $email = $this->resolveAdminNotificationEmail();
            if (empty($email)) {
                return;
            }

            $owner = User::find($reviewOwnerId);
            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsLine = $detailsTrimmed !== ''
                ? "\n".__('messages.ugc_admin_email_post_job_details_line', ['details' => $detailsTrimmed])
                : '';

            Mail::raw(
                __('messages.ugc_admin_email_body_review', [
                    'review_type' => ReviewReport::reviewTypeLabel($reviewType),
                    'review_id' => $reviewId,
                    'owner' => $owner ? ($owner->email ?? ('User ID '.$owner->id)) : 'Unknown',
                    'reporter' => $reporter->email,
                    'reason' => $reasonLabel,
                    'details_line' => $detailsLine,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($email) {
                    $message->to($email)->subject(__('messages.ugc_admin_email_subject_review'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function notifyReviewOwnerReported(int $reviewOwnerId, string $reviewType, string $reason, ?string $details): void
    {
        try {
            $owner = User::find($reviewOwnerId);
            if (! $owner || empty($owner->email)) {
                return;
            }

            $reasonLabel = $this->ugcReportReasonLabel($reason);
            $detailsTrimmed = $details ? trim($details) : '';
            $detailsBlock = $detailsTrimmed !== ''
                ? __('messages.ugc_job_owner_email_details_block', ['details' => $detailsTrimmed])."\n\n"
                : '';

            Mail::raw(
                __('messages.ugc_review_owner_email_body', [
                    'review_type' => ReviewReport::reviewTypeLabel($reviewType),
                    'reason' => $reasonLabel,
                    'details_block' => $detailsBlock,
                    'time' => now()->toDateTimeString(),
                ]),
                function ($message) use ($owner) {
                    $message->to($owner->email)->subject(__('messages.ugc_review_owner_email_subject'));
                }
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function ugcReportReasonLabel(string $reason): string
    {
        return UgcReportReasons::label($reason);
    }

    /**
     * List valid report reasons for mobile/web dropdowns (values match report/report-post-job validation).
     */
    public function reportReasons()
    {
        return response()->json(['reasons' => $this->reportReasonOptions()]);
    }
}
