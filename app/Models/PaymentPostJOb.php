<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentPostJOb extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'payment_post_jobs';
    protected $fillable = [ 'customer_id', 'datetime', 'post_job_bid_request_id', 'discount', 'total_amount', 'payment_type', 'txn_id', 'payment_status', 'other_transaction_detail' ];

    protected $casts = [
        'post_job_request_id'    => 'integer',
        'customer_id'   => 'integer',
        'discount'      => 'double',
        'total_amount'  => 'double',
    ];

    public function postJobRequest()
    {
        return $this->hasOneThrough(
            PostJobRequest::class,      // final model we want
            PostJobBid::class,          // intermediate model
            'id',                       // Foreign key on PostJobBid table (its primary key)
            'id',                       // Foreign key on PostJobRequest table (its primary key)
            'post_job_bid_request_id',  // Local key on PaymentPostJOb
            'post_request_id'           // Local key on PostJobBid that points to PostJobRequest
        );
    }
    public function postJobRequestBid(){
        return $this->belongsTo(PostJobBid::class, 'post_request_id', 'id');
    }
    public function postJobBidRequest()
    {
        return $this->belongsTo(PostJobBid::class, 'post_job_bid_request_id', 'id');
    }
    

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    public function scopeMyPayment($query)
    {
        $user = auth()->user();
    
        if($user->hasAnyRole(['admin', 'demo_admin'])){
            return $query;
        }
    
        if ($user->hasRole('provider')) {
            return $query->whereHas('postJobBidRequest', function($q) use($user) {
                $q->where('provider_id', $user->id);
            });
        }
        
    
        if($user->hasRole('user')) {
            return $query->where('payment_post_jobs.customer_id', $user->id);
        }
    }
    
}
