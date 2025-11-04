<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;


class FavouriteProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_id = auth()->user() ? (request()->customer_id ?? auth()->user()->id) : null;
        $providers_service_rating = 0;
        $total_service_rating = 0;
        if (optional($this->provider) && method_exists($this->provider, 'getServiceRating')) {
            $ratingsRelation = $this->provider->getServiceRating();
            // If relation loaded, use collection; else query avg/count to avoid N+1
            if ($this->provider->relationLoaded('getServiceRating')) {
                $providers_service_rating = (float) number_format(max($this->provider->getServiceRating->avg('rating'), 0), 2);
                $total_service_rating = $this->provider->getServiceRating->count();
            } else {
                $providers_service_rating = (float) number_format(max($ratingsRelation->avg('rating') ?? 0, 0), 2);
                $total_service_rating = (int) ($ratingsRelation->count() ?? 0);
            }
        }
        return [
            'id'            => $this->id,
            'provider_id'    => $this->provider_id,
            'is_favourite'  => 1,
            'profile_image'=> optional($this->provider)->login_type != null ? optional($this->provider)->social_image : getSingleMedia(optional($this->provider), 'profile_image',null),
            'display_name' => optional($this->provider)->display_name,
            'email' => optional($this->provider)->email,
            'contact_number' => optional($this->provider)->contact_number,
            'is_favourite'  => $this->where('user_id',$user_id)->first() ? 1 : 0,
            'providers_service_rating' => $providers_service_rating,
            'total_service_rating' => $total_service_rating,

        ];
    }
}
