<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia,SoftDeletes;
    protected $table = 'banks';
    protected $fillable = [
        'provider_id', 'bank_name', 'branch_name','account_no','account_holder' , 'mobile_no','iban_no','bic_number','status','stripe_account','is_default'
    ];

    protected $casts = [
        'provider_id'    => 'integer',
        'status'    => 'integer',
        'is_default' => 'integer',
    ];
    public function providers(){
        return $this->belongsTo('App\Models\User','provider_id','id')->withTrashed();
    }
    public function scopeMyBank($query){
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
            $query =  $query;
        }

        if($user->hasRole('provider')) {
            $query = $query->where('provider_id', $user->id);
        }
    }
    public function scopeList($query)
    {
        return $query->orderByRaw('deleted_at IS NULL DESC, deleted_at DESC')->orderBy('updated_at', 'desc');
    }
}
