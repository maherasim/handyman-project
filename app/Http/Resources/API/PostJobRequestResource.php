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
            'image'             => $this->image ? asset('storage/' . $this->image) : null,
            'images'            => is_array($this->images)
            ? array_map(fn($img) => asset('storage/' . $img), $this->images)
            : [],
            'total_views'       => $this->total_views,
            'country_id'        => $this->country_id,
            'city_id'           => $this->city_id,
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
