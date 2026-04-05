<?php

namespace App\Support;

use App\Models\ContentReport;
use App\Models\PostJobRequest;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UgcListing
{
    /**
     * Customers: Spatie role can be missing on legacy/imported accounts; user_type is authoritative (see PostJobRequest).
     */
    public static function isCustomer(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->user_type === 'user' || $user->hasRole('user');
    }

    /**
     * Providers (browsing jobs) or customers — eligible to load UGC scripts / report jobs (not services UI).
     */
    public static function isProviderUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->user_type === 'provider' || $user->hasRole('provider');
    }

    public static function isHandymanUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->user_type === 'handyman' || $user->hasRole('handyman');
    }

    /**
     * Report/block actions on a posted job (customer or provider, not the poster, not admin).
     */
    public static function canReportPostJob(?User $user, PostJobRequest $job): bool
    {
        if (! $user || ! $job->customer_id) {
            return false;
        }

        if ((int) $user->id === (int) $job->customer_id) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'demo_admin'])
            || in_array($user->user_type, ['admin', 'demo_admin'], true)) {
            return false;
        }

        return self::isCustomer($user) || self::isProviderUser($user) || self::isHandymanUser($user);
    }

    /** Load UGC scripts (report/block) on pages that need them. */
    public static function canLoadUgcScripts(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::isCustomer($user) || self::isProviderUser($user) || self::isHandymanUser($user);
    }

    /**
     * Public job listing (job-datatable): hide moderated posts and blocked customers (for providers/customers browsing).
     */
    public static function scopePublicPostJobs(Builder $query, ?int $viewerUserId = null): Builder
    {
        $user = Auth::user();
        $reportableType = PostJobRequest::class;

        $query->where(function ($q) use ($user, $reportableType) {
            $q->where(function ($inner) use ($reportableType) {
                $inner->whereRaw('COALESCE(post_job_requests.is_hidden_from_public, 0) = 0')
                    ->whereNotExists(function ($sub) use ($reportableType) {
                        $sub->select(DB::raw(1))
                            ->from('content_reports')
                            ->whereColumn('content_reports.reportable_id', 'post_job_requests.id')
                            ->where('content_reports.reportable_type', $reportableType)
                            ->where('content_reports.status', 'action_taken');
                    });
            });
            if ($user) {
                $q->orWhere('post_job_requests.customer_id', $user->id);
            }
        });

        $uid = $viewerUserId ?? ($user?->id);
        if ($uid) {
            $blockedIds = UserBlock::where('blocker_id', $uid)->pluck('blocked_id');
            if ($blockedIds->isNotEmpty()) {
                $query->whereNotIn('post_job_requests.customer_id', $blockedIds->all());
            }
        }

        return $query;
    }

    /**
     * Public/marketplace service listing: hide moderated listings and blocked providers (customers only).
     */
    public static function scopePublicServices(Builder $query, ?int $viewerUserId = null): Builder
    {
        $user = Auth::user();

        // Public storefront: hide moderated listings (including when an admin browses /service-list).
        // Uses services.is_hidden_from_public AND any admin "hide" report (defensive if the flag did not persist).
        $reportableType = Service::class;
        $query->where(function ($q) use ($user, $reportableType) {
            $q->where(function ($inner) use ($reportableType) {
                $inner->whereRaw('COALESCE(services.is_hidden_from_public, 0) = 0')
                    ->whereNotExists(function ($sub) use ($reportableType) {
                        $sub->select(DB::raw(1))
                            ->from('content_reports')
                            ->whereColumn('content_reports.reportable_id', 'services.id')
                            ->where('content_reports.reportable_type', $reportableType)
                            ->where('content_reports.status', 'action_taken');
                    });
            });
            if ($user && $user->hasRole('provider')) {
                $q->orWhere('services.provider_id', $user->id);
            }
        });

        $uid = $viewerUserId ?? ($user?->id);
        // Resolve viewer for role checks when the route has no auth middleware but caller passes auth()->id(),
        // or when optional Sanctum middleware set the user after scope was written (user is always from Auth when set).
        $viewer = $user ?? ($uid ? User::find($uid) : null);
        if ($uid && self::isCustomer($viewer)) {
            $blockedIds = UserBlock::where('blocker_id', $uid)->pluck('blocked_id');
            if ($blockedIds->isNotEmpty()) {
                $query->whereNotIn('services.provider_id', $blockedIds->all());
            }
        }

        return $query;
    }

    /**
     * Whether the viewer may load a single post job (matches scopePublicPostJobs for non-owners).
     * Owners always see their own listing; admins see all.
     */
    public static function canViewPostJobRequest(?User $user, PostJobRequest $job): bool
    {
        if (! $user || ! $job) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'demo_admin'])
            || in_array($user->user_type, ['admin', 'demo_admin'], true)) {
            return true;
        }

        if ((int) $user->id === (int) $job->customer_id) {
            return true;
        }

        if ((bool) ($job->is_hidden_from_public ?? false)) {
            return false;
        }

        $hasActionTaken = ContentReport::query()
            ->where('reportable_type', PostJobRequest::class)
            ->where('reportable_id', $job->id)
            ->where('status', 'action_taken')
            ->exists();
        if ($hasActionTaken) {
            return false;
        }

        $blockedIds = UserBlock::where('blocker_id', $user->id)->pluck('blocked_id');
        if ($blockedIds->isNotEmpty() && $blockedIds->contains($job->customer_id)) {
            return false;
        }

        return true;
    }

    public static function pendingReportsCountForService(int $serviceId): int
    {
        return ContentReport::query()
            ->where('reportable_type', Service::class)
            ->where('reportable_id', $serviceId)
            ->where('status', 'pending')
            ->count();
    }

    public static function pendingReportsCountForPostJob(int $postJobId): int
    {
        return ContentReport::query()
            ->where('reportable_type', PostJobRequest::class)
            ->where('reportable_id', $postJobId)
            ->where('status', 'pending')
            ->count();
    }
}
