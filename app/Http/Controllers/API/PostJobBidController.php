<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostJobRequest;
use App\Models\PostJobBid;
use App\Http\Resources\API\PostJobBiderResource;

class PostJobBidController extends Controller
{
    public function getPostBidList(Request $request){
        $user = auth()->user();
        $post_request = PostJobBid::myPostJobBid();
        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby: 'asc';

        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $post_request->count();
            }
        }

        $post_request = $post_request->orderBy('id',$orderBy)->paginate($per_page);
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
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        $query->with(['customer', 'category']);
    
        $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
        $data = $query->paginate($perPage);
    
        // Transform the items in the paginated result
        $data->getCollection()->transform(function ($item) {
            // ✅ Single image URL
            $item->image = $item->image ? asset('storage/' . ltrim($item->image, '/')) : null;
    
            // ✅ Multi-image URLs
            $images = [];
    
            if (!empty($item->images)) {
                // If it's a string, try to decode JSON
                if (is_string($item->images)) {
                    $decoded = json_decode($item->images, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $images = $decoded;
                    }
                } elseif (is_array($item->images)) {
                    $images = $item->images;
                }
            }
    
            // Convert each image to full URL
            $item->images = collect($images)
                ->filter() // remove null or empty values
                ->map(fn($img) => asset('storage/' . ltrim($img, '/')))
                ->values(); // reindex array
    
            return $item;
        });
    
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
    

}
