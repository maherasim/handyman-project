<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Service;
use App\Models\PostJobBid;


class PostJobRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      
        $user = auth()->user();
        $can_bid = null;
        if($user->hasRole('provider')){
          $can_bid = true;
          $count = PostJobBid::where('provider_id',$user->id)->where('post_request_id',$this->id)->get();
          if(count($count) > 0){
            $can_bid = false;
          }
        }
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'reason'            => $this->reason,
            'price'             => $this->price,
            'provider_id'       => $this->provider_id,
            'status'            => $this->status,
            'start_date'        => $this->start_date,
            'benefits'          => $this->benefits,
            'duties'            => $this->duties,
            'education_level'   => $this->education_level,
            'career_level'      => $this->career_level,
            'remote_work_level' => $this->remote_work_level,
            'job_schedule'      => $this->job_schedule,
            'working_address'   => $this->working_address,
            'street_address'    => $this->street_address,
            'house_number'      => $this->house_number,
            'total_budget'      => $this->total_budget,
            'price_type'        => $this->price_type,
            'image'             => $this->image,
            'images'            => $this->images,
            'accepted_bid_id'   => $this->accepted_bid_id,
            'advance_percent'   => $this->advance_percent,
            'remaining_percent' => $this->remaining_percent,
            'reason'            => $this->reason,
            'job_price'         => $this->job_price,
            'end_date'          => $this->end_date,
            'total_hours'       => $this->total_hours,
            'total_days'       => $this->total_days,
            'country_id'       => $this->country_id,
            'country' => optional($this->country)->name,
            'city_id'          => $this->city_id,
            'city'    => optional($this->city)->name,
       'images' => (function () {
    $images = [];

    if (!empty($this->images)) {
        if (is_string($this->images)) {
            $decoded = json_decode($this->images, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $images = $decoded;
            }
        } elseif (is_array($this->images)) {
            $images = $this->images;
        }
    }

    return collect($images)
        ->filter()
        ->map(fn($img) => asset('storage/' . ltrim($img, '/')))
        ->values();
})(),

            'state_id'          => $this->state_id,
            'requirement'      => $this->requirement,
            'type'             => $this->type,
            'category_id'       => $this->category_id,
            'subcategory_id'     => $this->subcategory_id,
            'customer_id'       => $this->customer_id,
            'customer_name'       => optional($this->customer)->display_name,
            'customer_profile'       => optional($this->customer)->login_type != null ? optional($this->customer)->social_image : getSingleMedia($this->customer, 'profile_image',null),
            'status'            => $this->status,
            'can_bid'           =>  $can_bid,
            'service'           => ServiceResource::collection(Service::whereIn('id',$this->postServiceMapping->pluck('service_id'))->get()),
            'job_price'             => $this->job_price,
            'total_views'          => (int) ($this->total_views ?? 0),
        ];
    }
}