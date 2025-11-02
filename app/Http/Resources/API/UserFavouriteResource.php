<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class UserFavouriteResource extends JsonResource {
    /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */

    public function toArray( $request ) {
        $user_id = auth()->user() ? ( request()->customer_id ?? auth()->user()->id ) : null;
        return [
            'id'            => $this->id,
            'service_id'    => $this->service_id,
            'user_id'       => $this->user_id,
            'created_at'    => date( 'Y-m-d', strtotime( $this->created_at ) ),
            'customer_name' => optional( $this->customer )->display_name,
            'name'          => optional( $this->service )->name,
            'description'   => optional( $this->service )->description,
            'price'         => optional( $this->service )->price,
            'price_format'  => getPriceFormat( optional( $this->service )->price ),
            'type'          => optional( $this->service )->type,
            'discount'      => optional( $this->service )->discount,
            'duration'      => optional( $this->service )->duration,
            'service_attchments' => getAttachments( optional( $this->service )->getMedia( 'service_attachment' ), null ),
            'is_favourite' => $this->service && $this->service->getUserFavouriteService
            ? $this->service->getUserFavouriteService->where( 'user_id', $user_id )->first() ? 1 : 0
            : 0,

            'total_rating' => $this->service && $this->service->serviceRating && $this->service->serviceRating->count() > 0
            ? ( float ) number_format( max( $this->service->serviceRating->avg( 'rating' ), 0 ), 2 )
            : 0,

            'total_views' => optional( $this->service )->total_views ?? 0,
            
            'completed_booking_count' => $this->service && $this->service->serviceBooking 
            ? $this->service->serviceBooking->where('status', 'completed')->count() 
            : 0,

            'category_name' => optional( optional( $this->service )->category )->name,

            'category_id'   => optional( $this->service )->category_id,

            'provider_image'  => optional( optional( $this->service )->providers )->login_type != null
            ? optional( optional( $this->service )->providers )->social_image
            : getSingleMedia( optional( optional( $this->service )->providers ), 'profile_image', null ),

            'provider_name'   => optional( optional( $this->service )->providers )->display_name,
            'provider_id'     => optional( optional( $this->service )->providers )->id,
        ];
    }
}
