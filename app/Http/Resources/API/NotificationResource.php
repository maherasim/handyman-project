<?php

namespace App\Http\Resources\API;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       $image = '';
        $booking = Booking::where('id',$this->data['id'])->first();
        if(!empty($booking)){
            $user = User::where('id',$booking->customer_id)->first();
            $image = $user->login_type != null ? $user->social_image: getSingleMedia($user, 'profile_image',null);
        }
        // Format timestamps in ISO 8601 with UTC timezone (Z format)
        $formatTimestamp = function($timestamp) {
            if (!$timestamp) {
                return null;
            }
            // Convert to UTC and format as ISO 8601 with Z suffix
            return $timestamp->utc()->format('Y-m-d\TH:i:s\Z');
        };

        return [
            'id' => $this->id,
            'read_at' => $formatTimestamp($this->read_at),
            'profile_image'     => $image,
            'created_at' => $formatTimestamp($this->created_at),
            'data' => $this->data,
        ];
    }
}
