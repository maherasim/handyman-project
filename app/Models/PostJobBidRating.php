<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobBidRating extends Model
{
    use HasFactory;

    public const STATUS_VISIBLE = 0;

    public const STATUS_HIDDEN = 1;

    protected $table = 'post_job_bid_ratings';

    protected $fillable = [
        'post_job_bid_id',
        'provider_id',
        'customer_id',
        'rating',
        'review',
        'status',
    ];

    protected $casts = [
        'post_job_bid_id' => 'integer',
        'provider_id' => 'integer',
        'customer_id' => 'integer',
        'rating' => 'double',
        'status' => 'integer',
    ];

    public function scopePublicVisible($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_VISIBLE)->orWhereNull('status');
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}


