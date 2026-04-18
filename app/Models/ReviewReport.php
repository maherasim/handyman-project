<?php

namespace App\Models;

use App\Support\UgcReportReasons;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReviewReport extends Model
{
    protected $fillable = [
        'reporter_id',
        'review_type',
        'review_id',
        'review_owner_id',
        'reason',
        'details',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(BookingRating::class, 'review_id');
    }

    public function customerReview(): BelongsTo
    {
        return $this->belongsTo(CustomerRating::class, 'review_id');
    }

    public function reviewOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_owner_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Load the concrete review row (polymorphic by review_type). */
    public function resolveReviewItem(): BookingRating|CustomerRating|PostJobBidRating|PostJobBidCustomerRating|HandymanRating|null
    {
        return match ($this->review_type) {
            'customer_rating' => CustomerRating::withTrashed()->find($this->review_id),
            'handyman_rating' => HandymanRating::withTrashed()->find($this->review_id),
            'post_job_bid_rating' => PostJobBidRating::query()->find($this->review_id),
            'post_job_bid_customer_rating' => PostJobBidCustomerRating::query()->find($this->review_id),
            default => BookingRating::withTrashed()->find($this->review_id),
        };
    }

    public function isReviewHidden(): bool
    {
        $reviewItem = $this->resolveReviewItem();
        if (! $reviewItem) {
            return false;
        }
        $byStatus = (int) ($reviewItem->status ?? 0) === 1;
        $legacyTrashed = method_exists($reviewItem, 'trashed') && $reviewItem->trashed();

        return $byStatus || $legacyTrashed;
    }

    /** Same reason vocabulary as profile reports (UgcSafetyController). */
    public static function reasonLabel(?string $reason): string
    {
        return UgcReportReasons::label($reason);
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => __('messages.review_report_status_pending'),
            'dismissed' => __('messages.review_report_status_dismissed'),
            'action_taken' => __('messages.review_report_status_action_taken'),
            'reviewed' => __('messages.review_report_status_reviewed'),
            default => $status ? Str::headline(str_replace('_', ' ', $status)) : '—',
        };
    }

    public static function reviewTypeLabel(?string $type): string
    {
        $types = __('messages.review_report_types');
        if (is_array($types) && isset($types[$type ?? ''])) {
            return $types[$type];
        }

        return $type ? Str::headline(str_replace('_', ' ', $type)) : '—';
    }
}
