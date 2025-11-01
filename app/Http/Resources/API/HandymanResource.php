<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\UserResource;
use App\Models\HandymanRating;
use App\Http\Resources\API\HandymanRatingResource;
class HandymanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
 public function toArray($request)
{
    $booking_id = request()->booking_id;
    $rating = null;
    if ($booking_id) {
        $r = HandymanRating::where('booking_id', $booking_id)
            ->where('handyman_id', $this->handyman->id)->first();
        $rating = $r ? new HandymanRatingResource($r) : null;
    }

    return [
        'handyman'        => new UserResource($this->handyman), // includes city_name, country_name
        'handyman_review' => $rating,
    ];
}
}
