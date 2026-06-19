<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HandymanCommissionRequest extends Model
{
    use SoftDeletes;

    protected $table = 'handyman_commission_requests';

    protected $fillable = [
        'handyman_id',
        'provider_id',
        'current_commission',
        'requested_commission',
        'status',
        'helpdesk_id',
        'provider_agreed',
        'handyman_agreed',
        'admin_notes',
    ];

    protected $casts = [
        'current_commission'   => 'float',
        'requested_commission' => 'float',
        'provider_agreed'      => 'boolean',
        'handyman_agreed'      => 'boolean',
    ];

    public function handyman()
    {
        return $this->belongsTo(User::class, 'handyman_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function helpdesk()
    {
        return $this->belongsTo(HelpDesk::class, 'helpdesk_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
