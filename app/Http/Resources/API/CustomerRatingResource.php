<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerRatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    { 
        return [
            'id'            => $this->id,
            'booking_id'    => $this->booking_id,
            'customer_id'   => $this->customer_id,
            'customer_name' => optional($this->customer)->display_name,
            'customer_profile_image' => optional($this->customer)->login_type != null ? optional($this->customer)->social_image : getSingleMedia($this->customer, 'profile_image', null),
            'provider_id'   => $this->provider_id,
            'provider_name' => optional($this->provider)->display_name,
            'provider_profile_image' => optional($this->provider)->login_type != null ? optional($this->provider)->social_image : getSingleMedia($this->provider, 'profile_image', null),
            'rating'        => $this->rating,
            'review'        => $this->review,
            'created_at'    => date('Y-m-d', strtotime($this->created_at)),
        ];
    }
}

