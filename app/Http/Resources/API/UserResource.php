<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserFavouriteProvider;
use App\Models\Booking;
use App\Models\Service;
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $providers_service_rating = (float) 0;
        $handyman_rating = (float) 0;
        $total_service_rating = 0;
        $is_verify_provider = false;
        $membership = 'free';
        $membership_icon = asset('images/freepng.png');
        $verified_sticker_icon = asset('images/icon/notverifiedpng.png');
        if($this->user_type == 'provider')
        {
            // Combined rating from service booking (booking_ratings) and post-job bid (post_job_bid_customer_ratings)
            $combined = \App\Models\User::getCombinedProviderRating((int) $this->id);
            $providers_service_rating = $combined['rating'];
            $total_service_rating = $combined['total_reviews'];
            $is_verify_provider = verify_provider_document($this->id);
            $verified_sticker_icon = $is_verify_provider
                ? asset('images/icon/verifiedpng.png')
                : asset('images/icon/notverifiedpng.png');
            if ($this->providerSubscription) {
                $rawPlan = strtolower(trim($this->providerSubscription->plan_type ?? $this->providerSubscription->title ?? ''));
                if (str_contains($rawPlan, 'silver')) {
                    $membership = 'silver';
                    $membership_icon = asset('images/icon/silverpng.png');
                } elseif (str_contains($rawPlan, 'gold')) {
                    $membership = 'gold';
                    $membership_icon = asset('images/goldpng.png');
                }
            }
            $handyman_rating = (isset($this->handymanRating) && count($this->handymanRating) > 0 ) ? (float) number_format(max($this->handymanRating->avg('rating'),0), 2) : 0;

        }
        if($this->user_type == 'handyman')
        {
            $handyman_rating = (isset($this->handymanRating) && count($this->handymanRating) > 0 ) ? (float) number_format(max($this->handymanRating->avg('rating'),0), 2) : 0;
        }
        if($this->login_type !== null && $this->login_type !== 'mobile' && $this->login_type !== 'user'){
            $profile_image = $this->social_image;
        }else{
            $profile_image = getSingleMedia($this, 'profile_image',null);
        }

        $whyDecoded = null;
        if (! empty($this->why_choose_me)) {
            $whyDecoded = json_decode($this->why_choose_me, true);
            if (! is_array($whyDecoded)) {
                $whyDecoded = null;
            }
        }
        $aboutTitle = $whyDecoded['title'] ?? $whyDecoded['why_choose_me_title'] ?? null;
        $aboutDesc = $whyDecoded['about_description'] ?? null;
        $aboutReasons = $whyDecoded['reason'] ?? $whyDecoded['why_choose_me_reason'] ?? null;
        if (is_array($aboutReasons)) {
            $aboutReasons = array_values(array_filter($aboutReasons, static function ($v) {
                return $v !== null && $v !== '';
            }));
        } else {
            $aboutReasons = null;
        }

        return [
            'id'                => $this->id,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'username'          => $this->username,
            'provider_id'       => $this->provider_id,
            'status'            => $this->status,
            'description'       => $this->description,
            'user_type'         => $this->user_type,
            'email'             => $this->email,
            'contact_number'    => $this->contact_number,
            'country_id'        => $this->country_id,
            'state_id'          => $this->state_id,
            'city_id'           => $this->city_id,
            'city_name'         => optional($this->city)->name,
            'state_name'        => optional($this->state)->name,
            'country_name'      => optional($this->country)->name,
            'city'              => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null,
            'state'             => $this->state ? ['id' => $this->state->id, 'name' => $this->state->name] : null,
            'country'           => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null,
            'address'           => $this->address,
            'status'            => $this->status,
            'providertype_id'   => $this->providertype_id,
            'providertype'      => optional($this->providertype)->name,
            'is_featured'       => $this->is_featured,
            'display_name'      => $this->display_name,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
            'profile_image'     => $profile_image,
            'time_zone'         => $this->time_zone,
            'uid'               => $this->uid,
            'login_type'        => $this->login_type,
            'service_address_id'=> $this->service_address_id,
            'last_notification_seen' => $this->last_notification_seen,
            'providers_service_rating' => $providers_service_rating,
            'total_service_rating' => $total_service_rating,
            'handyman_rating' => $handyman_rating,
            'is_verify_provider' => (int) $is_verify_provider,
            // provider stickers (for providers)
            'provider_verified' => (int) $is_verify_provider,
            'verified_sticker_icon' => $verified_sticker_icon,
            'membership' => $membership,
            'membership_icon' => $membership_icon,
            'isHandymanAvailable' =>  $this->is_available,
            'languages' =>  $this->languages,
            'about_me' =>  $this->about_me,
            'vat_number' =>  $this->vat_number,
            'company_name' =>  $this->company_name,
            'designation' => $this->designation,
            'handymantype_id' => $this->handymantype_id,
            'handymantype' => optional($this->handymantype)->name,
            'handyman_commission' => (string) $this->handyman_commission,
            'known_languages' => $this->known_languages,
            'language_option' => $this->language_option,
            'availability' => $this->availability,
            'experience' => $this->experience,
            'years_of_experience' => $this->years_of_experience,
            'mobility' => $this->mobility,
            'education' => $this->education,
            'career_level' => $this->career_level,
            'certification' => $this->certification,
            'skills' => $this->skills,
            'is_favourite'  => UserFavouriteProvider::where('user_id',$request->login_user_id ?? null)->where('provider_id',$request->id ?? $this->id)->first() ? 1 : 0,
            'total_services_booked' => Booking::where('provider_id',$this->id)->count('service_id'),
            // Total Services: Count of active services created by this provider
            'total_services' => ($this->user_type == 'provider') 
                ? Service::where('provider_id', $this->id)
                    ->where('service_type', 'service')
                    ->where('status', 1)
                    ->count() 
                : 0,
            // Total Bookings: Count of all bookings for this provider
            'total_bookings' => ($this->user_type == 'provider') 
                ? Booking::where('provider_id', $this->id)->count() 
                : 0,
            // Completed Jobs: Count of completed bookings for this provider
            'completed_jobs' => ($this->user_type == 'provider') 
                ? Booking::where('provider_id', $this->id)->where('status', 'completed')->count() 
                : 0,
            'why_choose_me' => $this->why_choose_me,
            // Decoded (same as web profile_form / Flutter listing)
            'title' => $aboutTitle,
            'about_description' => $aboutDesc,
            'reason' => $aboutReasons,
            'is_subscribe' => $this->is_subscribe,
            'is_email_verified' => $this->is_email_verified

        ];
    }
}
