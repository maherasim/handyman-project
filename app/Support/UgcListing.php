<?php

namespace App\Support;

use App\Models\ContentReport;
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
        if ($uid && self::isCustomer($user)) {
            $blockedIds = UserBlock::where('blocker_id', $uid)->pluck('blocked_id');
            if ($blockedIds->isNotEmpty()) {
                $query->whereNotIn('services.provider_id', $blockedIds->all());
            }
        }

        return $query;
    }

    public static function pendingReportsCountForService(int $serviceId): int
    {
        return ContentReport::query()
            ->where('reportable_type', Service::class)
            ->where('reportable_id', $serviceId)
            ->where('status', 'pending')
            ->count();
    }
}
