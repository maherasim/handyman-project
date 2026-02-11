<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobBidRating extends Model
{
    use HasFactory;

    protected $table = 'post_job_bid_ratings';

    protected $fillable = [
        'post_job_bid_id',
        'provider_id',
        'customer_id',
        'rating',
        'review',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}


