<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSlot extends Model
{
    protected $table = 'service_slots';
    protected $guarded = [];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
