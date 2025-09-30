<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Service;


class PostJobBiderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'post_request_id'       => $this->post_request_id,
            'provider_id'           => $this->provider_id,
            'price'                 => $this->price,
            'duration'              => $this->duration,
            'why_choose_me'   => $this->why_choose_me,
            'provider'              => new UserResource($this->provider),
            'post_detail'           => new PostJobRequestResource($this->postrequest)
        ];
    }
    public function apiIndex(Request $request)
{
    $query = PostJobRequest::query();

    // Role-based visibility
    if (!auth()->user()->hasAnyRole(['admin']) && auth()->user()->user_type !== 'provider') {
        $query->where('customer_id', auth()->id());
    }

    // Optional filtering (status, category, etc.)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // Eager-load related models
    $query->with(['customer', 'category']);

    // Return paginated response
    return response()->json([
        'success' => true,
        'data' => $query->paginate(10),
    ]);
}

}