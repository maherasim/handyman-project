<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PaymentPostJOb extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'payment_post_jobs';
    protected $fillable = [ 'customer_id','post_job_request_id', 'datetime', 'post_job_request_id', 'discount', 'total_amount', 'payment_type', 'txn_id', 'payment_status', 'other_transaction_detail' ];

    protected $casts = [
        'post_job_request_id'    => 'integer',
        'customer_id'   => 'integer',
        'discount'      => 'double',
        'total_amount'  => 'double',
    ];

    public function postJobRequest(){
        return $this->belongsTo(PostJobRequest::class, 'post_job_request_id', 'id');
    }

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
