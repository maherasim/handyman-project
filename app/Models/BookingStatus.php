<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStatus extends Model
{
    use HasFactory;
    protected $table = 'booking_statuses';
    protected $fillable = [
        'value','label', 'sequence', 'status'
    ];
    
    protected $casts = [
        'status'    => 'integer',
        'sequence'  => 'integer',
    ];

    public static function bookingStatus($status)
    {
        $label = static::query()->where('value', $status)->value('label');
        if (!empty($label)) {
            return $label;
        }
        // Fallback to a humanized version of the raw status value
        if (is_string($status) && $status !== '') {
            return ucwords(str_replace('_', ' ', $status));
        }
        return '';
    }
}
