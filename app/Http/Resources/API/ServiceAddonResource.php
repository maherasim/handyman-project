<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Booking;

class ServiceAddonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $hasBooking = Booking::where('service_id', $this->service_id)->exists();

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'service_id'    => $this->service_id,
            'service_name'  => $this->service->name,
            'price'         => $this->price,
            'status'        => $this->status,
            'serviceaddon_image' => optional($this->getMedia('serviceaddon_image')->first())->getUrl(),
            'show_edit_delete_buttons' => !$hasBooking,
        ];
    }
}
