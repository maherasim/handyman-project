<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BookingStatus;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $extraValue = 0;
        if($this->bookingExtraCharge->count() > 0){
            foreach($this->bookingExtraCharge as $chrage){
                $extraValue += $chrage->price * $chrage->qty;
            }
        }
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $datetime = json_decode($sitesetup->value);
        $payment = $this->payment()->orderBy('id','desc')->first();
        // Provider stickers: verified and membership
        $provider = optional($this->provider);
        $providerId = $provider ? $provider->id : null;
        $isVerified = $providerId ? verify_provider_document($providerId) : false;
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
        // Get payment status and type to determine if address should be shown
        // Address is visible when payment status is 'paid' or 'advanced_paid' (for any payment type)
        $paymentStatus = $payment ? strtolower($payment->payment_status ?? '') : '';
        $paymentType = $payment ? strtolower($payment->payment_type ?? '') : '';
        
        $showAddress = in_array($paymentStatus, ['paid', 'advanced_paid'], true);
        
        // Return professional message instead of null when address is not available
        $addressValue = $showAddress ? $this->address : 'Available after payment confirmation';
        
        return [
            'id'                    => $this->id,
            'address'               => $addressValue,
            'customer_id'           => $this->customer_id,
            'service_id'            => $this->service_id,
            'provider_id'           => $this->provider_id,
            'date'                  => $this->date,
            'booking_date'          => date("$datetime->date_format $datetime->time_format", strtotime($this->date)),
            'price'                 => optional($this->service)->price,
            'type'                  => optional($this->service)->type,
            'discount'              => optional($this->service)->discount,
            'status'                => $this->status,
            'country_name'          => optional($this->service->country)->name,
            'city_name'             => optional($this->service->city)->name,
            'visit_type'            => optional($this->service)->visit_type,
            'status_label'          => BookingStatus::bookingStatus($this->status),
            'description'           => $this->description,
            'provider_name'         => optional($this->provider)->display_name,
            'customer_name'         => optional($this->customer)->display_name,
            'service_name'          => optional($this->service)->name,
            'job_type'          => optional($this->service)->type,
            'payment_id'            => $this->payment_id,
            'payment_status'        => $payment ? $payment->payment_status : null,
            'payment_method'        => $payment ? $payment->payment_type : null,
            'provider_name'         => optional($this->provider)->display_name ?? null,
            'customer_name'         => optional($this->customer)->display_name ?? null,
            'provider_image'        => getSingleMedia($this->provider, 'profile_image',null),
            'provider_is_verified'  => (bool) optional($this->provider)->is_verified,
            // new sticker fields
            'provider_verified'     => $isVerified ? 1 : 0,
            'verified_sticker_icon' => $verifiedIcon,
            'membership'            => $membership,
            'membership_icon'       => $planIcon,
            'customer_image'        => getSingleMedia($this->customer, 'profile_image',null),
            'service_name'          => optional($this->service)->name ?? null,
            'handyman'              => isset($this->handymanAdded) ? $this->handymanAdded->map(function($handymanMapping) {
                $handyman = $handymanMapping->handyman;
                // augment handyman object
                $handyman->handyman_image = getSingleMedia($handyman, 'profile_image', null);
                $handyman->is_verified = $handyman->is_verified ? 1 : 0;
                // provide names directly and override ids as requested for handyman card
                $handyman->city_name = optional($handyman->city)->name;
                $handyman->country_name = optional($handyman->country)->name;
                $handyman->city_id = $handyman->city_name;
                $handyman->country_id = $handyman->country_name;
                return $handymanMapping;
            }) : [],
            'service_attchments'    => getAttachments(optional($this->service)->getMedia('service_attachment'),null),
            'duration_diff'         => $this->duration_diff,
            'booking_address_id'    => $this->booking_address_id,
            'duration_diff_hour'    => ($this->service->type === 'hourly') ? convertToHoursMins($this->duration_diff) : null,
            'taxes'                 => $this->getTaxData($this->tax),
            'quantity'              => $this->quantity,
            'coupon_data'           => isset($this->couponAdded) ? $this->couponAdded : null,
            'total_amount'          => $this->total_amount,
            'total_rating'          => (float) number_format(max(optional($this->service)->serviceRating->avg('rating'),0), 2),
            'amount'                => $this->amount,
            'extra_charges'         => BookingChargesResource::collection($this->bookingExtraCharge),
            'extra_charges_value'   => $extraValue,
            'booking_type'          => $this->type,
            'booking_slot'          => $this->booking_slot,
            'total_review'          => optional($this->service)->serviceRating->count(),
            'booking_package'       => new BookingPackageResource($this->bookingPackage),
            'advance_paid_amount'   => $this->advance_paid_amount == null ? 0:(double) $this->advance_paid_amount,
            'advance_payment_amount'=> optional($this->service)->advance_payment_amount == null ? 0:(bool) optional($this->service)->advance_payment_amount,

        ];
    }

    private function getTaxData()
    {
        $taxData = json_decode($this->tax, true);
        if (is_array($taxData)) {
            $taxData = array_map(function ($item) {
                $item['id'] = (int) $item['id'];
                $item['value'] = (float) $item['value'];
                return $item;
            }, $taxData);
        } else {           
            $taxData = []; 
        }
    
        return $taxData;
    }
}
