<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRating extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'customer_ratings';
    
    protected $fillable = [
        'booking_id', 'customer_id', 'provider_id', 'rating', 'review'
    ];

    protected $casts = [
        'booking_id'    => 'integer',
        'customer_id'   => 'integer',
        'provider_id'   => 'integer',
        'rating'        => 'double',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function scopeMyRating($query){
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            return $query;
        }

        if($user->hasRole('provider')) {
            return $query->where('provider_id', $user->id);
        }

        if($user->hasRole('user')) {
            return $query->where('customer_id', $user->id);
        }

        return $query;
    }
}

