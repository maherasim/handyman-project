<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProfileReport extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
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

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Human-readable reason (maps API values like misleading_profile_or_skills). */
    public static function reasonLabel(?string $reason): string
    {
        if ($reason === null || $reason === '') {
            return '—';
        }
        $labels = __('messages.profile_report_reasons');
        if (is_array($labels) && isset($labels[$reason])) {
            return $labels[$reason];
        }

        return Str::headline(str_replace('_', ' ', $reason));
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => __('messages.profile_report_status_pending'),
            'dismissed' => __('messages.profile_report_status_dismissed'),
            'action_taken' => __('messages.profile_report_status_action_taken'),
            default => $status ? Str::headline(str_replace('_', ' ', $status)) : '—',
        };
    }
}
