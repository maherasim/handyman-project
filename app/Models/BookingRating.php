<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BookingRating extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'booking_ratings';
    public const STATUS_VISIBLE = 0;

    public const STATUS_HIDDEN = 1;

    protected $fillable = [
        'booking_id', 'customer_id', 'service_id', 'rating', 'review', 'status',
    ];

    protected $casts = [
        'booking_id'    => 'integer',
        'service_id'    => 'integer',
        'customer_id'   => 'integer',
        'rating'        => 'double',
        'status'        => 'integer',
    ];

    /**
     * Visible on public service pages and listings (status 0; null treated as visible for legacy rows).
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
    
       public function scopeMyRating($query){
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            return $query;
        }

        if($user->hasRole('provider')) {
            return $query->whereHas('handyman', function($q) use ($user) {
                $q->where('provider_id', $user->id);
            });
        }

        if($user->hasRole('user')) {
            return $query->where('customer_id', $user->id);
        }

        if($user->hasRole('handyman')) {
            return $query->where('handyman_id',$user->id);
        }

        return $query;
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function service(){
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
