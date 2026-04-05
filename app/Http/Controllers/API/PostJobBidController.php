<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use App\Models\PostJobBid;
use App\Http\Resources\API\PostJobBiderResource;
use App\Support\UgcListing;

class PostJobBidController extends Controller
{
    public function getPostBidList(Request $request){
        $user = auth()->user();
        $post_request = PostJobBid::myPostJobBid();
        $per_page = config('constant.PER_PAGE_LIMIT');

		// Always show latest bids first

        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $post_request->count();
            }
        }

		$post_request = $post_request->orderBy('created_at','desc')->orderBy('id','desc')->paginate($per_page);
        $items = PostJobBiderResource::collection($post_request);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }
 
    public function apiIndex(Request $request)
    {
        $query = PostJobRequest::query();
    
        // Role-based visibility
        if (!auth()->user()->hasAnyRole(['admin']) && auth()->user()->user_type !== 'provider') {
            $query->where('customer_id', auth()->id());
        }

        // Providers (and non-admins): hide moderated jobs (is_hidden_from_public), admin-action reports, blocked customers — same as web job-datatable
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'demo_admin'])
            || in_array($user->user_type ?? '', ['admin', 'demo_admin'], true);
        if (! $isAdmin) {
            UgcListing::scopePublicPostJobs($query, auth()->id());
        }
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        // Eager load relationships and count proposals
        $query->with([
            'customer.city',
            'customer.state',
            'customer.country',
            'category',
            'city',
            'state',
            'country'
        ])->withCount('proposals'); // ✅ proposals_count will be available
    
		$perPage = $request->input('per_page', 10);
		// Newest posts first for provider view
		$data = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);
    
        $data->getCollection()->transform(function ($item) {
            $item->image = $item->image ? asset('storage/' . ltrim($item->image, '/')) : null;
    
            $images = [];
    
            if (!empty($item->images)) {
                if (is_string($item->images)) {
                    $decoded = json_decode($item->images, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $images = $decoded;
                    }
                } elseif (is_array($item->images)) {
                    $images = $item->images;
                }
            }
    
            $item->images = collect($images)
                ->filter()
                ->map(fn($img) => asset('storage/' . ltrim($img, '/')))
                ->values();
    
            // Location names
            $item->city_name = optional($item->city)->name;
            $item->state_name = optional($item->state)->name;
            $item->country_name = optional($item->country)->name;
    
            // Customer location names
            if ($item->customer) {
                $item->customer->city_name = optional($item->customer->city)->name;
                $item->customer->state_name = optional($item->customer->state)->name;
                $item->customer->country_name = optional($item->customer->country)->name;
            }
    
            // Proposal count (already available from withCount)
            $item->proposals_count = $item->proposals_count ?? 0;
    
            return $item;
        });
    
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
    
    
    

}
