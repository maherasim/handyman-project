<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ServicePackage;
use App\Models\Setting;
class ServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_id = isset(request()->customer_id) ? (request()->customer_id ?? auth()->user()->id) : null;
        $image = getSingleMedia($this,'service_attachment', null);
        $file_extention = config('constant.IMAGE_EXTENTIONS');
        $extention = in_array(strtolower(imageExtention($image)),$file_extention);
        $servicepackage = collect(); // Initialize as an empty collection
        if (!empty($this->servicePackage)) {
            $servicepackageIds = $this->servicePackage->pluck('service_package_id');

            // Query the ServicePackage model using the plucked service_package_id values
            $servicepackage = ServicePackage::whereIn('id', $servicepackageIds)
                ->where('status', 1)
                ->where(function ($query) {
                    $query->where('end_at', '>=', now()->toDateString())
                        ->orWhereNull('end_at'); // Include packages with null end_at
                })
                ->get();
        }

        $serviceconfig = Setting::getValueByKey('service-configurations','service-configurations');

        $advancePaymentPercentage = $serviceconfig->advance_paynment_percantage ?? 0;
        $global_advance_payment = $serviceconfig->global_advance_payment ?? 0;


        // Provider stickers
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
                $planIcon = asset('images/icon/goldpng.png');
                $membership = 'gold';
            }
        }

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'subcategory_id'   => $this->subcategory_id,
            'provider_id'   => $this->provider_id,
            'price'         => $this->price,
            'price_format'  => getPriceFormat($this->price),
            'type'          => $this->type,
            'city_id'          => $this->city_id,
            'country_id'          => $this->country_id,
            'city_name' => optional($this->city)->name,
            'country_name' => optional($this->country)->name,
            'discount'      => $this->discount,
            'minimum_booking'      => $this->minimum_booking,
            'duration'      => $this->duration,
            'status'        => $this->status,
            'views'        => $this->total_views,
            'travel_required' => $this->travel_required,
            'remote_work_level' => $this->remote_work_level,
            'career_level' => $this->career_level,
            'description'   => $this->description,
            'cancellation_policy'   => $this->cancellation_policy,
            'is_featured'   => $this->is_featured,
            'provider_name' => optional($this->providers)->name,
            // provider stickers
            'provider_verified' => $isVerified ? 1 : 0,
            'verified_sticker_icon' => $verifiedIcon,
            'membership' => $membership,
            'membership_icon' => $planIcon,
            'category_name'  => optional($this->category)->name,
            'subcategory_name'  => optional($this->subcategory)->name,
            'attchments' => getAttachments($this->getMedia('service_attachment'),null),
            'attchments_array' => getAttachmentArray($this->getMedia('service_attachment'),null),
            'total_review'  => $this->serviceRating->count('id'),
            'total_rating'  => count($this->serviceRating) > 0 ? (float) number_format(max($this->serviceRating->avg('rating'),0), 2) : 0,
            'total_booking_count' => $this->serviceBooking->count(),
            'completed_booking_count' => $this->serviceBooking->where('status', 'completed')->count(),
            'is_favourite'  => $this->getUserFavouriteService->where('user_id',$user_id)->first() ? 1 : 0,
            'service_address_mapping' => $this->providerServiceAddress->map(function($mapping) {
                return array_merge($mapping->toArray(), [
                    'city_name' => optional($this->city)->name,
                    'country_name' => optional($this->country)->name,
                ]);
            }),
            'attchment_extension' => $extention,
            'deleted_at' => $this->deleted_at,
            'is_slot'           => $this->is_slot,
            'slots'              => getServiceTimeSlot($this->provider_id ),
            'servicePackage'    => ServicePackageResource::collection($servicepackage),
            'visit_type'           => $this->visit_type,
            'is_enable_advance_payment' => $this->is_enable_advance_payment == 1 ? $this->is_enable_advance_payment : $global_advance_payment ,
            'advance_payment_percentage' => $this->is_enable_advance_payment == 1 ? ($this->advance_payment_amount === null ? '0%' : (double) $this->advance_payment_amount . '%') : (double) $advancePaymentPercentage . '%',
            'total_views' => (int) ($this->total_views ?? 0),
        ];
    }
}
