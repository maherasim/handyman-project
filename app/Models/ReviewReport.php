<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
