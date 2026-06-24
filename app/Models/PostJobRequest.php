<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PostJobRequest extends Model
{
    use HasFactory;
    protected $table = 'post_job_requests';
    protected $fillable = [
        'title', 'type','customer_id', 'status' ,'description','provider_id','advance_percent','remaining_percent ', 'reason','price','total_budget','date','job_price','price_type','working_address','street_address','house_number', 'country_id','state_id','city_id','category_id','subcategory_id','start_date','end_date','total_hours','total_days','requirement','job_schedule','remote_work_level','career_level','travel_required','education_level','duties','benefits','image','images','total_views','accepted_bid_id','cancel_bid_id', 'is_hidden_from_public',
    ];

    protected $casts = [
        'customer_id'  => 'integer',
        'provider_id'  => 'integer',
        'is_hidden_from_public' => 'boolean',
        'price' => 'double',
        'total_budget' => 'double',
        'job_price' => 'string',
        'price_type' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'image' => 'string',
        'images' => 'array',
        'travel_required' => 'boolean',
    ];
    public function postServiceMapping(){
        return $this->hasMany(PostJobServiceMapping::class, 'post_request_id','id');
    }
    public function scopeMyPostJob($query)
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $isAdmin = $user->hasRole('admin')
            || $user->hasRole('demo_admin')
            || in_array($user->user_type, ['admin', 'demo_admin'], true);

        if ($isAdmin) {
            return $query;
        }

        if ($user->hasRole('provider') || $user->user_type === 'provider') {
            return $query;
        }

        // Customers: Spatie role can be missing on legacy/imported accounts; user_type is authoritative
        if ($user->hasRole('user') || $user->user_type === 'user') {
            return $query->where('customer_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
    
    public function getTotalBidsAttribute()
    {
        if ($this->relationLoaded('postBidList')) {
            return $this->getRelation('postBidList')->count();
        }
        return $this->postBidList()->count();
    }
        public function postBidList(){
            return $this->hasMany(PostJobBid::class, 'post_request_id','id');
        }
        public function proposals()
{
    return $this->hasMany(PostJobBid::class, 'post_request_id');
}

        public function provider(){
            return $this->belongsTo(User::class,'provider_id', 'id')->withTrashed();
        }
        public function customer(){
            return $this->belongsTo(User::class,'customer_id', 'id')->withTrashed();
        }
        public function category()
        {
            return $this->belongsTo(Category::class);
        }
        public function subCategory()
        {
            return $this->belongsTo(SubCategory::class,'subcategory_id','id');
        }
        public function country(){
            return $this->belongsTo(Country::class, 'country_id','id');
        }
        public function state(){
            return $this->belongsTo(State::class, 'state_id','id');
        }
        public function city(){
            return $this->belongsTo(City::class, 'city_id','id');
        }
        public function bids()
{
    return $this->hasMany(PostJobBid::class, 'post_request_id');
}

}
