<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Setting;
use App\Models\Booking;
use App\Models\Service;

class ServiceResource extends JsonResource
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

        $image = getSingleMedia($this, 'service_attachment', null);
        $file_extention = config('constant.IMAGE_EXTENTIONS');
        $extention = in_array(strtolower(imageExtention($image)), $file_extention);

        $serviceconfig = Setting::getValueByKey('service-configurations', 'service-configurations');
        $advancePaymentPercentage = $serviceconfig->advance_paynment_percantage ?? 0;
        $global_advance_payment = $serviceconfig->global_advance_payment ?? 0;

        $provider = optional($this->providers);
        $isVerified = $provider ? verify_provider_document($provider->id) : false;
        $verifiedIcon = $isVerified
            ? asset('images/icon/verifiedpng.png')
            : asset('images/icon/notverifiedpng.png');

        $planIcon = asset('images/freepng.png');
        $membership = 'free';
        if ($provider && $provider->providerSubscription) {
            $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
            if (str_contains($rawPlan, 'silver')) {
                $planIcon = asset('images/icon/silverpng.png');
                $membership = 'silver';
            } elseif (str_contains($rawPlan, 'gold')) {
                $planIcon = asset('images/goldpng.png');
                $membership = 'gold';
            }
        }

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'subcategory_id'=> $this->subcategory_id,
            'provider_id'   => $this->provider_id,
            'total_booking_count' => Booking::where('service_id', $this->id)->count(),

            'price'         => $this->price,
            'price_format'  => getPriceFormat($this->price),
            'type'          => $this->type,
            'discount'      => $this->discount,
            'duration'      => $this->duration,
            'status'        => $this->status,
            'cancellation_policy' => $this->cancellation_policy,
            'description'   => $this->description,
            'is_featured'   => $this->is_featured,
            'provider_name' => optional($this->providers)->display_name,
            'provider_image' => optional($this->providers)->login_type != null 
                ? optional($this->providers)->social_image 
                : getSingleMedia(optional($this->providers), 'profile_image', null),
            'city_id' => optional($this->providers)->city_id,
            // provider location names for mobile UI
            'city_name' => optional(optional($this->providers)->city)->name,
            'country_name' => optional(optional($this->providers)->country)->name,
            // provider verification & membership stickers
            'provider_verified' => $isVerified ? 1 : 0,
            'verified_sticker_icon' => $verifiedIcon,
            'membership' => $membership,
            'membership_icon' => $planIcon,
            // service location names (if needed by some views)
            'service_city_name' => optional($this->city)->name,
            'service_country_name' => optional($this->country)->name,
            'category_name'  => optional($this->category)->name,
            'subcategory_name'  => optional($this->subcategory)->name,
            'attchments' => getAttachments($this->getMedia('service_attachment')),
            'attchments_array' => getAttachmentArray($this->getMedia('service_attachment'), null),
            'total_review'  => count($this->serviceRatingPublic),
            'total_rating'  => count($this->serviceRatingPublic) > 0
                ? (float) number_format(max($this->serviceRatingPublic->avg('rating'), 0), 2)
                : 0,
            'is_favourite'  => $this->getUserFavouriteService->where('user_id', $user_id)->first() ? 1 : 0,
            'service_address_mapping' => $this->providerServiceAddress->map(function($mapping) {
                return array_merge($mapping->toArray(), [
                    'city_name' => optional($this->city)->name,
                    'country_name' => optional($this->country)->name,
                ]);
            }),
            'attchment_extension' => $extention, // true: for png, false: other
            'deleted_at'        => $this->deleted_at,
            'is_slot'           => $this->is_slot,
            'slots'             => getServiceTimeSlot($this->provider_id),
            'visit_type'        => $this->visit_type,
            'remote_work_level' => $this->remote_work_level,
            'career_level'      => $this->career_level,
            'travel_required'   => $this->travel_required,
            'is_enable_advance_payment' => $this->is_enable_advance_payment ? $this->is_enable_advance_payment : $global_advance_payment,
            'advance_payment_amount' => $this->is_enable_advance_payment 
                ? ($this->advance_payment_amount === null ? 0 : (double) $this->advance_payment_amount) 
                : (double) $advancePaymentPercentage,
            'completed_booking_count' =>   Booking::where('service_id', $this->id)->count(),
            'total_services_booked' => Booking::where('service_id', $this->id)->count(),
            'total_views' => (int) ($this->total_views ?? 0),
            'provider_total_services' => $this->provider_total_services ?? Service::where('provider_id', $this->provider_id)
                ->where('service_type', 'service')
                ->where('status', 1)
                ->count(),
        ];
    }
}
