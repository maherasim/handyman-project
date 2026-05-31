<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRating extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'customer_ratings';
    
    public const STATUS_VISIBLE = 0;

    public const STATUS_HIDDEN = 1;

    protected $fillable = [
        'booking_id', 'customer_id', 'provider_id', 'rating', 'review', 'status',
    ];

    protected $casts = [
        'booking_id'    => 'integer',
        'customer_id'   => 'integer',
        'provider_id'   => 'integer',
        'rating'        => 'double',
        'status'        => 'integer',
    ];

    /**
     * Visible publicly (status 0; null treated as visible for legacy rows).
     */
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

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id')->withTrashed();
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

    /**
     * Backend list: admin sees all; customers (user_type user) only see ratings they received.
     */
    public function scopeListForBackend($query)
    {
        $user = auth()->user();
        if ($user && $user->hasAnyRole(['admin', 'demo_admin'])) {
            return $query;
        }
        if ($user && $user->user_type === 'user') {
            return $query->where('customer_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
}

