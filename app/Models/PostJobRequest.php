<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PostJobRequest extends Model
{
    use HasFactory;
    protected $table = 'post_job_requests';
        protected $fillable = [
        'title', 'type','customer_id', 'status' ,'description','provider_id','advance_percent','remaining_percent ', 'reason','price','total_budget','date','job_price','price_type','country_id','state_id','city_id','category_id','subcategory_id','start_date','end_date','total_hours','total_days','requirement','job_schedule','remote_work_level','career_level','travel_required','education_level','duties','benefits','image','images','total_views'
    ];

    protected $casts = [
        'customer_id'  => 'integer',
        'provider_id'  => 'integer',
        'price' => 'double',
        'total_budget' => 'double',
        'job_price' => 'string',
        'price_type' => 'string',
        'image' => 'string',
        'images' => 'array',
        'travel_required' => 'boolean',
    ];
    public function postServiceMapping(){
        return $this->hasMany(PostJobServiceMapping::class, 'post_request_id','id');
    }
    public function scopeMyPostJob($query)
    {
        if(auth()->user()->hasRole('admin')) {
            return $query;
        }

        if(auth()->user()->hasRole('user')) {
            return $query->where('customer_id', \Auth::id());
        }
        if(auth()->user()->hasRole('provider')) {
            return $query;
        }
        return $query;
    }
    public function getTotalBidsAttribute()
    {
        return $this->postBidList()->count();
    }
        public function postBidList(){
            return $this->hasMany(PostJobBid::class, 'post_request_id','id');
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
}
