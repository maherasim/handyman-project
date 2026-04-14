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
        if (function_exists('booking_detail_status_label')) {
            $label = booking_detail_status_label($status);
            if (($status === null || $status === '') && $label === '—') {
                return '';
            }

            return $label;
        }

        $label = static::query()->where('value', $status)->value('label');
        if (!empty($label)) {
            return $label;
        }
        if (is_string($status) && $status !== '') {
            return ucwords(str_replace('_', ' ', $status));
        }

        return '';
    }
}
