<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandymanPayout extends Model
{
    use HasFactory;
    protected $table = 'handyman_payouts';
    protected $fillable = [
        'handyman_id', 'payment_method', 'description','amount','status','payment_id'
    ];

    protected $casts = [
        'handyman_id'     => 'integer',
        'amount'    => 'double',
        'payment_id'  => 'integer',
    ];
    public function handymans(){
        return $this->belongsTo(User::class, 'handyman_id','id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id')->withTrashed();
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id')->withTrashed();
    }
    public function scopeMyPayout($query)
    {
        if(auth()->user()->hasRole('admin')) {
            return $query;
        }

        if(auth()->user()->hasRole('handyman')) {
            return $query->where('handyman_id', \Auth::id());
        }

        return $query;
    }
}
