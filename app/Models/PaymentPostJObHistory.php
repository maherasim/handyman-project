<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPostJObHistory extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'payment_post_job_histories';
    protected $fillable = ['payment_id', 'post_job_request_id',
        'action', 'text', 'type', 'sender_id', 'receiver_id', 'datetime', 'status','total_amount',
        'txn_id','other_transaction_detail','parent_id'];

    protected $casts = [
        'payment_id' => 'integer',
        'post_job_request_id' => 'integer',
    ];

    public function PaymentPostJOb(){
        return $this->belongsTo(PaymentPostJOb::class, 'payment_id', 'id');
    }

    public function sender(){
        return $this->belongsTo(User::class,'sender_id', 'id')->withTrashed();
    }

    public function receiver(){
        return $this->belongsTo(User::class,'receiver_id', 'id')->withTrashed();
    }
    public function postJobRequest(){
        return $this->belongsTo(PostJobRequest::class, 'post_job_request_id', 'id');
    }
 
}
