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
        $isVerified = false;
        $membership = 'free';
        $membership_icon = asset('images/freepng.png');
        $verified_sticker_icon = asset('images/icon/notverifiedpng.png');
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
        if (optional($this->provider)) {
            $isVerified = verify_provider_document($this->provider->id);
            $verified_sticker_icon = $isVerified
                ? asset('images/icon/verifiedpng.png')
                : asset('images/icon/notverifiedpng.png');
            if ($this->provider->providerSubscription) {
                $rawPlan = strtolower(trim($this->provider->providerSubscription->plan_type ?? $this->provider->providerSubscription->title ?? ''));
                if (str_contains($rawPlan, 'silver')) {
                    $membership = 'silver';
                    $membership_icon = asset('images/icon/silverpng.png');
                } elseif (str_contains($rawPlan, 'gold')) {
                    $membership = 'gold';
                    $membership_icon = asset('images/goldpng.png');
                }
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
            'provider_verified' => $isVerified ? 1 : 0,
            'verified_sticker_icon' => $verified_sticker_icon,
            'membership' => $membership,
            'membership_icon' => $membership_icon,

        ];
    }
}
