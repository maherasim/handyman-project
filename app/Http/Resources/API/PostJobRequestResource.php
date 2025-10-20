<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Service;
use App\Models\Tax;

class PostJobRequestResource extends JsonResource
{
    public function toArray($request)
    {
        $user = auth()->user();
        $can_bid = null;

        if ($user->hasRole('provider')) {
            $can_bid = true;
            $count = count($this->postBidList->where('provider_id', $user->id));
            if ($count > 0) {
                $can_bid = false;
            }
        }

        // Fetch tax percent based on country_id
        $tax = Tax::where('id', $this->country_id)->first();
        $tax_percent = $tax ? $tax->value . '%' : null;

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'reason'            => $this->reason,
            'price'             => $this->price,
            'provider_id'       => $this->provider_id,
            'customer_id'       => $this->customer_id,
            'status'            => $this->status,
            'bid_count'         => $this->postBidList->count(),
            'type'              => $this->type,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'total_hours'       => $this->total_hours,
            'total_days'        => $this->total_days,
            
            'images' => (function () {
    $images = [];

    if (!empty($this->images)) {
        if (is_string($this->images)) {
            $decoded = json_decode($this->images, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $images = $decoded;
            }
        } elseif (is_array($this->images)) {
            $images = $this->images;
        }
    }

    return collect($images)
        ->filter()
        ->map(fn($img) => asset('storage/' . ltrim($img, '/')))
        ->values();
})(),

            'total_views'       => $this->total_views,
           'country' => optional($this->country)->name,
            'city'    => optional($this->city)->name,

            'requirement'       => $this->requirement,
            'category_id'       => $this->category_id,
            'subcategory_id'    => $this->subcategory_id,
            'can_bid'           => $can_bid,
            'service'           => ServiceResource::collection(
                Service::whereIn('id', $this->postServiceMapping->pluck('service_id'))->get()
            ),
            'created_at'        => $this->created_at,
            'job_price'         => $this->job_price,
            'accepted_bid_id'   => $this->accepted_bid_id,
            'tax_percent'       => $tax_percent, // ✅ New field
        ];
    }
}
