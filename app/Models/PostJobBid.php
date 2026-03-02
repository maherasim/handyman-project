<?php

namespace App\Models;
use App\Models\PostJobRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobBid extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_request_id', 'provider_id', 'why_choose_me','extra_charges', 'price' ,'duration','customer_id','title','status','advance_percent','remaining_percent ', 'hold_reason','quantity'
    ];

    protected $casts = [
        'post_request_id'  => 'integer',
        'provider_id'  => 'integer',
        'customer_id'  => 'integer',
        'price' => 'double',
    ];

        public function request()
{
    return $this->belongsTo(PostJobRequest::class, 'post_request_id');
}
    public function provider(){
        return $this->belongsTo(User::class,'provider_id', 'id')->withTrashed();
    }


    public function customer(){
        return $this->belongsTo(User::class, 'customer_id', 'id')->withTrashed();
    }
    public function scopeMyPostJobBid($query)
    {
        if(auth()->user()->hasRole('admin')) {
            return $query;
        }

        if(auth()->user()->hasRole('user')) {
            return $query->where('customer_id', \Auth::id());
        }
        if(auth()->user()->hasRole('provider')) {
            return $query->where('provider_id', \Auth::id());
        }
        return $query;
    }
    public function postrequest(){
        return $this->belongsTo(PostJobRequest::class,'post_request_id', 'id');
    }
    public function extraCharges()
    {
        return $this->hasMany(PostJobExtraCharge::class, 'post_job_bid_id');
    }

    /** Provider rates customer: rows in post_job_bid_ratings where provider_id is the rater. */
    public function ratings()
    {
        return $this->hasMany(PostJobBidRating::class, 'post_job_bid_id')
            ->whereColumn('post_job_bid_ratings.provider_id', 'post_job_bids.provider_id');
    }

    /** Customer rates provider: rows in post_job_bid_ratings where customer_id is the rater. */
    public function customerRatings()
    {
        return $this->hasMany(PostJobBidRating::class, 'post_job_bid_id')
            ->whereColumn('post_job_bid_ratings.customer_id', 'post_job_bids.customer_id');
    }
}
