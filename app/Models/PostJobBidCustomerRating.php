<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobBidCustomerRating extends Model
{
    use HasFactory;

    protected $table = 'post_job_bid_customer_ratings';

    protected $fillable = [
        'post_job_bid_id',
        'provider_id',
        'customer_id',
        'rating',
        'review',
    ];

    public function postJobBid()
    {
        return $this->belongsTo(PostJobBid::class, 'post_job_bid_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
