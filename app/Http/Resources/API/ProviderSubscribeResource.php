<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSubscribeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $plan = $this->plan;

        return [
            'id'                => $this->id,
            'plan_id'           => $this->plan_id,
            'title'             => $plan->title ?? $this->title,
            'identifier'        => $plan->identifier ?? $this->identifier,
            'type'              => $plan->type ?? $this->type,
            'trial_period'      => $plan->trial_period ?? 0,
            'amount'            => $this->amount,
            'txn_id'            => optional($this->payment)->txn_id,
            'status'            => $this->status,
            'start_at'          => $this->start_at,
            'end_at'            => $this->end_at,
            'duration'          => $plan->duration ?? $this->duration,
            'description'       => $plan->description ?? $this->description,
            'plan_type'         => $plan->plan_type ?? $this->plan_type,
            'plan_limitation'   => json_decode($this->plan_limitation, true),
        ];
    }
}
