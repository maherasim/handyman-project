<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSubscription extends Model
{
    use HasFactory;
    protected $table = 'provider_subscriptions';
    protected $fillable = [
        'plan_id', 'user_id', 'title','identifier', 'type','start_at','end_at','amount','status','payment_id','plan_limitation','duration','description','plan_type'
    ];
    protected $casts = [
        'amount'    => 'double',
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'payment_id' => 'integer',
    ];
    public function payment(){
        return $this->belongsTo(SubscriptionTransaction::class, 'subscription_plan_id','id');
    }
    public function getPlanIconAttribute()
{
    switch (strtolower($this->plan_type)) {
        case 'free plan':
            return asset('images/icon/freepng.png');
        case 'Silver plan':
            return asset('images/icon/silverpng.png');
        case 'Gold plan':
            return asset('images/icon/goldpng.png');
        default:
            return asset('images/icons/default.png');
    }
}
public function providerSubscription()
{
    return $this->hasOne(ProviderSubscription::class);
}
}
