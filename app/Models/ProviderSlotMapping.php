<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSlotMapping extends Model
{
    use HasFactory;
    protected $table = 'provider_slot_mappings';
    protected $fillable = [
       'provider_id', 'date','start_at','end_at','status','days'
    ];
    protected $casts = [
        'provider_id' => 'integer',
        'status' => 'integer',
        'date' => 'date',
        'start_at' => 'time',
        'end_at' => 'time',
        'days' => 'string',
    ];   
    public function providerslots(){
        return $this->belongsTo(User::class,'provider_id', 'id');
    }
}
