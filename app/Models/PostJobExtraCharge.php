<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobExtraCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_job_bid_id',
        'title',
        'amount',
        'quantity',
    ];

    protected $casts = [
        'post_job_bid_id' => 'integer',
        'amount' => 'double',
        'quantity' => 'double',
    ];

    public function bid()
    {
        return $this->belongsTo(PostJobBid::class, 'post_job_bid_id');
    }
}

