<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\BookingRating;
use App\Models\HandymanRating;
use App\Models\CustomerRating;
use App\Models\PostJobBidCustomerRating;
use App\Models\PostJobBidRating;
use App\Models\BookingActivity;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\LiveLocation;
use App\Models\User;
use App\Models\BookingHandymanMapping;
use App\Models\ServiceProof;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\BookingDetailResource;
use App\Http\Resources\API\BookingRatingResource;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\UserResource;
use App\Http\Resources\API\HandymanResource;
use App\Http\Resources\API\HandymanRatingResource;
use App\Http\Resources\API\CustomerRatingResource;
use App\Http\Resources\API\ServiceProofResource;
use App\Http\Resources\API\PostJobRequestResource;
use App\Models\BookingServiceAddonMapping;
use App\Traits\NotificationTrait;
use App\Traits\EarningTrait;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusUpdateMail;
use Carbon\Carbon;
use App\Models\Setting;

class BookingController extends Controller
{
    use NotificationTrait;
    use EarningTrait;
    public function getBookingList(Request $request){
        $booking = Booking::myBooking()->with(
            'customer',
            'provider',
            'provider.providerSubscription',
            'service',
            'payment',
            'handymanAdded.handyman.city',
            'handymanAdded.handyman.country',
            'service.city',
            'service.country'
        );

        if ($request->has('provider_id') && !empty($request->provider_id)) {
            $provider_ids = is_array($request->provider_id) ? $request->provider_id : explode(',', $request->provider_id);
            $booking->whereIn('provider_id', $provider_ids);
        }

        if ($request->has('handyman_id') && !empty($request->handyman_id)) {
            $handyman_ids = is_array($request->handyman_id) ? $request->handyman_id : explode(',', $request->handyman_id);
            $booking->whereHas('handymanAdded', function($query) use ($handyman_ids) {
                $query->whereIn('handyman_id', $handyman_ids);
            });
        }

        if ($request->has('service_id') && !empty($request->service_id)) {
            $service_ids = is_array($request->service_id) ? $request->service_id : explode(',', $request->service_id);
            $booking->whereIn('service_id', $service_ids);
        }

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $customer_ids = is_array($request->customer_id) ? $request->customer_id : explode(',', $request->customer_id);
            $booking->whereIn('customer_id', $customer_ids);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $booking->whereDate('date', '>=', date('Y-m-d', strtotime($request->date_from)));
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $booking->whereDate('date', '<=', date('Y-m-d', strtotime($request->date_to)));
        }

        if ($request->has('status') && isset($request->status)) {

            $status = explode(',', $request->status);
            $booking->whereIn('status', $status);

         }

         if ($request->has('payment_status') && isset($request->payment_status)) {
            $payment_status = explode(',', $request->payment_status);
            $booking->whereHas('payment', function($query) use ($payment_status) {
                $query->whereIn('payment_status', $payment_status);
            });
         }

         if ($request->has('payment_type') && isset($request->payment_type)) {
            $payment_type = explode(',', $request->payment_type);
            $booking->whereHas('payment', function($query) use ($payment_type) {
                $query->whereIn('payment_type', $payment_type);
            });
         }

         if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $booking->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")

                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'LIKE', "%$search%");
                    })

                    ->orWhereHas('provider', function ($providerQuery) use ($search) {
                        $providerQuery->where(function ($nameQuery) use ($search) {
                            $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                                ->orWhere('email', 'LIKE', "%$search");
                        });
                     })

                     ->orWhereHas('customer', function ($userQuery) use ($search) {
                        $userQuery->where(function ($nameQuery) use ($search) {
                            $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                                ->orWhere('email', 'LIKE', "%$search");
                        });
                    });
            });
        }



        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $booking->count();
            }
        }
        $orderBy = 'desc';
        if( $request->has('orderby') && !empty($request->orderby)){
            $orderBy = $request->orderby;
        }

        $booking = $booking->orderBy('updated_at',$orderBy)->paginate($per_page);
        $items = BookingResource::collection($booking);

        // Initialize total earning
        $total_earning = 0;

        // Initialize payment breakdown
        $payment_breakdown = [
            'admin_earned' => 0,
            'handyman_earned' => 0,
            'provider_earned' => 0,
            'tax' => 0,
            'discount' => 0
        ];



        // Calculate totals from the booking items
        foreach ($items as $booking) {

            if($booking->status != 'cancelled' && $booking->handymanAdded->count() > 0){

                $total_earning += $booking->total_amount ?? 0;

                // Get commission data for this booking
                if ($booking->commissionsdata) {
                    foreach ($booking->commissionsdata as $commission) {
                        switch ($commission->user_type) {
                              case 'admin':
                              case 'demo_admin':
                                $payment_breakdown['admin_earned'] += $commission->commission_amount ?? 0;
                                break;
                            case 'provider':
                                $payment_breakdown['provider_earned'] += $commission->commission_amount ?? 0;
                                break;
                            case 'handyman':
                                $payment_breakdown['handyman_earned'] += $commission->commission_amount ?? 0;
                                break;
                        }
                    }
                }

                $payment_breakdown['tax'] += $booking->final_total_tax ?? 0;
                $payment_breakdown['discount'] += ($booking->final_discount_amount) ?? 0;

            }


        }

        // Format all numbers to 2 decimal places
        $total_earning = number_format($total_earning, 2, '.', '');
        array_walk($payment_breakdown, function(&$value) {
            $value = number_format($value, 2, '.', '');
        });

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
            'total_earning' => $total_earning,
            'payment_breakdown' => $payment_breakdown
        ];

        return comman_custom_response($response);
    }


    public function getBookingDetail(Request $request){

        // Get booking_id from request - try multiple methods to handle JSON and form data
        $id = $request->input('booking_id');
        
        // If input() returns null, try getting from all() (for JSON requests)
        if ($id === null) {
            $all = $request->all();
            $id = $all['booking_id'] ?? null;
        }
        
        // Validate booking_id - check if it's set and not empty string
        if ($id === null || $id === '') {
            $message = __('messages.booking_id_required') ?? 'Booking ID is required';
            return comman_message_response($message, 400);
        }
        
        // Cast to integer to ensure proper type
        $id = (int) $id;
        
        if ($id <= 0) {
            $message = __('messages.invalid_booking_id') ?? 'Invalid booking ID';
            return comman_message_response($message, 400);
        }

        // First check if booking exists at all (without relationships for debugging)
        $bookingExists = Booking::withTrashed()->where('id', $id)->exists();
        
        if (!$bookingExists) {
            $message = __('messages.booking_not_found') ?? 'Booking not found';
            return comman_message_response($message, 404);
        }

        // Now load with all relationships
        $booking_data = Booking::withTrashed()->with([
          'customer',
          'provider.city','provider.country','provider.providerSubscription',
          'handymanAdded.handyman.city','handymanAdded.handyman.country',
          'service','bookingRating','bookingPostJob','bookingAddonService',
          'bookingPackage','payment','slots',
        ])->where('id', $id)->first();
        
        if($booking_data == null){
            $message = __('messages.booking_not_found') ?? 'Booking not found';
            return comman_message_response($message, 404);
        }
        $booking_detail = new BookingDetailResource($booking_data);

        $rating_data = BookingRatingResource::collection($booking_detail->bookingRating->take(5));
        $service = new ServiceResource($booking_detail->service);

        $customer_id = (int) $booking_detail->customer_id;

        // Get customer rating info from customer_ratings (booking) and post_job_bid_ratings (provider rates customer)
        $customerRatingInfo = CustomerRating::where('customer_id', $customer_id)
            ->publicVisible()
            ->with(['provider', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
        $postJobBidRatings = PostJobBidRating::where('customer_id', $customer_id)
            ->publicVisible()
            ->with(['provider', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Map to same shape as CustomerRatingResource and merge
        $bookingCustomer = $booking_detail->customer;
        $mapBookingRating = function ($r) use ($bookingCustomer) {
            return [
                'id' => $r->id,
                'booking_id' => $r->booking_id ?? null,
                'customer_id' => $r->customer_id,
                'customer_name' => optional($bookingCustomer)->display_name ?? optional($r->customer)->display_name,
                'customer_profile_image' => optional($bookingCustomer)->login_type != null ? optional($bookingCustomer)->social_image : getSingleMedia($bookingCustomer ?? $r->customer, 'profile_image', null),
                'provider_id' => $r->provider_id,
                'provider_name' => optional($r->provider)->display_name,
                'provider_profile_image' => optional($r->provider)->login_type != null ? optional($r->provider)->social_image : getSingleMedia($r->provider, 'profile_image', null),
                'rating' => $r->rating,
                'review' => $r->review,
                'created_at' => $r->created_at ? date('Y-m-d', strtotime($r->created_at)) : null,
            ];
        };
        $allCustomerReviews = $customerRatingInfo->map(function ($r) use ($mapBookingRating) {
            $arr = $mapBookingRating($r);
            $arr['booking_id'] = $r->booking_id;
            return $arr + ['_sort_at' => $r->created_at];
        })->concat($postJobBidRatings->map(function ($r) use ($mapBookingRating) {
            $arr = $mapBookingRating($r);
            $arr['booking_id'] = null;
            return $arr + ['_sort_at' => $r->created_at];
        }))->sortByDesc('_sort_at')->values();

        $customerAverageRating = $allCustomerReviews->count() > 0 ? $allCustomerReviews->avg('rating') : 0;
        $customerTotalRatings = $allCustomerReviews->count();

        // Recent customer reviews (last 10) - same JSON shape as before
        $customerReviews = $allCustomerReviews->take(10)->map(function ($item) {
            unset($item['_sort_at']);
            return $item;
        })->values();

        $customer = new UserResource($booking_detail->customer);
        $customerArray = $customer->toArray($request);
        $customerArray['customer_rating'] = round($customerAverageRating, 1);
        $customerArray['customer_total_ratings'] = $customerTotalRatings;
        $customerArray['customer_reviews'] = $customerReviews;
        
        $provider_data = new UserResource($booking_detail->provider);
        $provider_data = $provider_data->toArray($request);

        // Provider reviews from booking_ratings and post_job_bid_customer_ratings (same combined logic as UserResource rating)
        $provider_id = (int) $booking_detail->provider_id;
        $providerBookingIds = Booking::where('provider_id', $provider_id)->pluck('id');
        $bookingRatingsForProvider = $providerBookingIds->isEmpty()
            ? collect()
            : BookingRating::whereIn('booking_id', $providerBookingIds)
                ->publicVisible()
                ->with(['customer', 'service'])
                ->orderBy('created_at', 'desc')
                ->get();
        $postJobBidRatingsForProvider = PostJobBidCustomerRating::where('provider_id', $provider_id)
            ->publicVisible()
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $mapBookingRatingToReview = function ($r) {
            $customer = $r->customer;
            $profile_image = optional($customer)->login_type != null ? (optional($customer)->social_image ?? getSingleMedia($customer, 'profile_image', null)) : getSingleMedia($customer, 'profile_image', null);
            return [
                'id' => $r->id,
                'rating' => $r->rating,
                'review' => $r->review,
                'service_id' => $r->service_id ?? null,
                'service_name' => optional($r->service)->name ?? null,
                'attchments' => isset($r->service) ? getAttachments($r->service->getMedia('service_attachment')) : [],
                'booking_id' => $r->booking_id ?? null,
                'created_at' => $r->created_at ? date('Y-m-d', strtotime($r->created_at)) : null,
                'customer_id' => $r->customer_id,
                'customer_name' => optional($customer)->display_name,
                'profile_image' => $profile_image,
                '_sort_at' => $r->created_at,
            ];
        };
        $mapPostJobRatingToReview = function ($r) {
            $customer = $r->customer;
            $profile_image = optional($customer)->login_type != null ? (optional($customer)->social_image ?? getSingleMedia($customer, 'profile_image', null)) : getSingleMedia($customer, 'profile_image', null);
            return [
                'id' => $r->id,
                'rating' => $r->rating,
                'review' => $r->review,
                'service_id' => null,
                'service_name' => null,
                'attchments' => [],
                'booking_id' => null,
                'created_at' => $r->created_at ? date('Y-m-d', strtotime($r->created_at)) : null,
                'customer_id' => $r->customer_id,
                'customer_name' => optional($customer)->display_name,
                'profile_image' => $profile_image,
                '_sort_at' => $r->created_at,
            ];
        };
        $allProviderReviews = $bookingRatingsForProvider->map($mapBookingRatingToReview)
            ->concat($postJobBidRatingsForProvider->map($mapPostJobRatingToReview))
            ->sortByDesc('_sort_at')
            ->values();
        $provider_reviews = $allProviderReviews->take(10)->map(function ($item) {
            unset($item['_sort_at']);
            return $item;
        })->values();

        $provider_data['provider_reviews'] = $provider_reviews;

        $handyman_data = HandymanResource::collection($booking_detail->handymanAdded);

        // Customer's review of the provider for THIS booking — always load by booking_id so both customer and provider see it
        $review_by_customer_for_booking = BookingRating::where('booking_id', $id)->with('customer')->first();
        $review_by_customer_payload = $review_by_customer_for_booking ? (new BookingRatingResource($review_by_customer_for_booking))->toArray($request) : null;

        $customer_review = null;
        if($request->customer_id != null){
            $customer_review = BookingRating::where('customer_id',$request->customer_id)->where('service_id',$booking_detail->service_id)->where('booking_id',$id)->first();
            if (!empty($customer_review))
            {
                $customer_review = new BookingRatingResource($customer_review);
            }
        }

        if (auth()->check()) {
            $auth_user = auth()->user();
            if (count($auth_user->unreadNotifications) > 0) {
                $auth_user->unreadNotifications->where('data.id', $id)->markAsRead();
            }
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $booking_activity = BookingActivity::where('booking_id',$id)->orderBy('id', 'asc')->get();
        $serviceProof = ServiceProofResource::collection(ServiceProof::with('service','handyman','booking')->where('booking_id',$id)->get());
        $post_job_object = null;
        if($booking_data->type == 'user_post_job'){
            $post_job_object = new PostJobRequestResource($booking_data->bookingPostJob);
        }

        $bookingpackage = $booking_data->bookingPackage;
        if($bookingpackage !== null){
            $mediaUrl = $bookingpackage->package->getFirstMedia('package_attachment')?->getUrl();
            $bookingpackage->package_image = $mediaUrl ?? asset('images/default.png');
        }

        // Check if customer rating already exists for this booking
        // Return meaningful message instead of true/false
        $rating_exists = CustomerRating::where('booking_id', $id)->exists();
        $show_rate_customer_button = $rating_exists
            ? 'Rating already submitted'
            : 'Rate Customer';

        // Provider's review of the customer for THIS booking (from customer_ratings)
        $provider_review = CustomerRating::where('booking_id', $id)
            ->with('provider')
            ->first();
        $provider_review_payload = null;
        if ($provider_review) {
            $provider_review_payload = [
                'id' => $provider_review->id,
                'booking_id' => $provider_review->booking_id,
                'rating' => (float) $provider_review->rating,
                'review' => $provider_review->review,
                'provider_id' => $provider_review->provider_id,
                'provider_name' => optional($provider_review->provider)->display_name,
                'provider_profile_image' => optional($provider_review->provider)->login_type != null
                    ? optional($provider_review->provider)->social_image
                    : getSingleMedia($provider_review->provider, 'profile_image', null),
                'created_at' => $provider_review->created_at ? $provider_review->created_at->format('Y-m-d') : null,
            ];
        }

        $response = [
            'booking_detail'    => $booking_detail,
            'service'           => $service,
            'customer'          => $customerArray,
            'booking_activity'  => $booking_activity,
            'rating_data'       => $rating_data,
            'handyman_data'     => $handyman_data,
            'provider_data'     => $provider_data,
            'coupon_data'       => $booking_detail->couponAdded,
            'customer_review'   => $customer_review,
            'review_by_customer_for_booking' => $review_by_customer_payload,
            'provider_review'   => $provider_review_payload,
            'service_proof'     => $serviceProof,
            'post_request_detail' => $post_job_object,
            'show_rate_customer_button' => $show_rate_customer_button,
            // 'bookingpackage'    => $bookingpackage,
        ];
        return comman_custom_response($response);
    }

    public function saveBookingRating(Request $request)
    {

        $rating_data = $request->all();
        $result = BookingRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('messages.update_form',[ 'form' => __('messages.rating') ] );
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.rating') ] );
		}

        return comman_message_response($message);
    }

    public function deleteBookingRating(Request $request)
    {
        $user = \Auth::user();

        $book_rating = BookingRating::where('id',$request->id)->where('customer_id',$user->id)->delete();

        $message = __('messages.delete_form',[ 'form' => __('messages.rating') ] );

        return comman_message_response($message);
    }

    public function bookingStatus(Request $request)
    {
        $allowed = ['en', 'de', 'fr', 'it', 'pt', 'es', 'ar', 'nl'];
        $lang = $request->query('lang');
        if (is_string($lang) && in_array($lang, $allowed, true)) {
            App::setLocale($lang);
        }

        $booking_status = BookingStatus::orderBy('sequence')->get()->map(function ($row) {
            $key = 'messages.booking_status_option_' . $row->value;
            if (\Illuminate\Support\Facades\Lang::has($key)) {
                $row->label = __($key);
            }

            return $row;
        });

        return comman_custom_response($booking_status);
    }

    public function bookingUpdate(Request $request)
    {
        $setting = Setting::getValueByKey('site-setup','site-setup');
        $digitafter_decimal_point = $setting ? $setting->digitafter_decimal_point : "2";
        $data = $request->all();

        $id = $request->id;
       

        try {
            $data['start_at'] = $request->filled('start_at')
                ? Carbon::parse($request->start_at)->format('Y-m-d H:i:s')
                : null;
        } catch (\Throwable $e) { $data['start_at'] = null; }
        
        try {
            $data['end_at'] = $request->filled('end_at')
                ? Carbon::parse($request->end_at)->format('Y-m-d H:i:s')
                : null;
        } catch (\Throwable $e) { $data['end_at'] = null; }
        $data['cancellation_charge'] = isset($request->cancellation_charge) ? $request->cancellation_charge : 0;
        $data['cancellation_charge_amount'] = isset($request->cancellation_charge_amount) ? $request->cancellation_charge_amount : 0;

        $bookingdata = Booking::with(['customer', 'provider', 'service', 'handymanAdded.handyman', 'payment'])->find($id);

        // Capture old status immediately after fetching booking data, before any modifications
        $old_status = $bookingdata->status;
        $activity_type = null; // Initialize activity_type

        $paymentdata = Payment::where('booking_id',$id)->first();
        // Cancel happens before advance payment – booking update does not touch customer wallet.
        // Normalize client-provided payment_status for safe comparisons
        $clientPaymentStatus = strtolower(str_replace(' ', '_', $data['payment_status'] ?? ''));
        // Determine if the actor is the assigned provider
        $actorIsProvider = auth()->check() && auth()->user()->hasAnyRole(['provider']) && auth()->id() === $bookingdata->provider_id;

        if($request->type == 'service_addon'){
            if($request->has('service_addon') && $request->service_addon != null ){
                foreach($request->service_addon as $serviceaddon){
                    $get_addon = BookingServiceAddonMapping::where('id',$serviceaddon)->first();
                    $get_addon->status = 1;
                    $get_addon->update();
                }
                $message = __('messages.update_form',[ 'form' => __('messages.booking') ] );

                if($request->is('api/*')) {
                    return comman_message_response($message);
                }
            }
        }
        if($request->has('service_addon') && $request->service_addon != null ){
            foreach($request->service_addon as $serviceaddon){
                $get_addon = BookingServiceAddonMapping::where('id',$serviceaddon)->first();
                $get_addon->status = 1;
                $get_addon->update();
            }
        }
        if($data['status'] === 'hold'){
            if($bookingdata->start_at == null && $bookingdata->end_at == null){
                $duration_diff = $data['duration_diff'];
                $data['duration_diff'] = $duration_diff;
            }else{
                if($bookingdata->status == $data['status']){
                    $booking_start_date = $bookingdata->start_at;
                    $request_start_date = $data['start_at'];
                    if($request_start_date > $booking_start_date){
                        $msg = __('messages.already_in_status',[ 'status' => $data['status'] ] );
                        return comman_message_response($msg);
                    }
                }else{
                    $duration_diff = $bookingdata->duration_diff;
                    if($bookingdata->start_at != null && $bookingdata->end_at != null){
                        $new_diff = $data['duration_diff'];
                    }else{
                        $new_diff = $data['duration_diff'];
                    }
                    $data['duration_diff'] = $duration_diff + $new_diff;
                    $bookingdata['duration_diff'] = $data['duration_diff'];
                    $data['final_total_service_price'] = round($bookingdata->getServiceTotalPrice(),$digitafter_decimal_point);
                    $data['final_discount_amount'] = round($bookingdata->getDiscountValue(),$digitafter_decimal_point);
                    $data['final_coupon_discount_amount'] = round($bookingdata->getCouponDiscountValue(),$digitafter_decimal_point);
                    $subtotal = $bookingdata->getSubTotalValue() + $bookingdata->getServiceAddonValue();;
                    $data['final_sub_total'] = $subtotal;
                    $tax = round($bookingdata->getTaxesValue(),$digitafter_decimal_point);
                    $data['final_total_tax'] = $tax;
                        // without include extrachage tax caculation
                    $totalamount =   $subtotal + $tax;;
                    $data['total_amount'] =round($totalamount,$digitafter_decimal_point);
                }
            }
        }
        if($data['status'] === 'pending_approval'){
            //  echo $bookingdata->getSubTotalValue();
            // echo  $bookingdata->getServiceAddonValue();
            // return 'working';

            $duration_diff = $bookingdata->duration_diff;
            $new_diff = $data['duration_diff'];

            $data['duration_diff'] = $duration_diff + $new_diff;
            $bookingdata['duration_diff'] = $data['duration_diff'];
            $data['final_total_service_price'] = round($bookingdata->getServiceTotalPrice(),$digitafter_decimal_point);
            $data['final_discount_amount'] = round($bookingdata->getDiscountValue(),$digitafter_decimal_point);
            $data['final_coupon_discount_amount'] = round($bookingdata->getCouponDiscountValue(),$digitafter_decimal_point);
            $subtotal = $bookingdata->getSubTotalValue() + $bookingdata->getServiceAddonValue();
            $data['final_sub_total'] = $subtotal;
            $tax = round($bookingdata->getTaxesValue(),$digitafter_decimal_point);
            $data['final_total_tax'] = $tax;
                // without include extrachage tax caculation
            $totalamount =   $subtotal + $tax;
            $data['total_amount'] =round($totalamount,$digitafter_decimal_point);

        }

        if($data['status'] == 'rejected'){
            if($bookingdata->handymanAdded()->count() > 0){
                $assigned_handyman_ids = $bookingdata->handymanAdded()->pluck('handyman_id')->toArray();
                $bookingdata->handymanAdded()->delete();
                $data['status'] = 'accept';
            }
        }
        if($data['status'] == 'pending'){
            if($bookingdata->handymanAdded()->count() > 0){
                $bookingdata->handymanAdded()->delete();
                $data['status'] = 'accept';
            }
        }

        // Set activity_type based on final status change (after all modifications)
        if($old_status != $data['status']) {
            if($data['status'] == 'cancelled'){
                $activity_type = 'cancel_booking';
            } else {
                $activity_type = 'update_booking_status';
            }
        }

        // Cancel/reject: mark payment as Cancelled. Wallet not used – cancel happens before advance payment.
        if(($data['status'] == 'rejected' || $data['status'] == 'cancelled') && (($clientPaymentStatus == 'advance_paid') || (optional($paymentdata)->payment_status == 'advance_paid'))){
            $paymentData = Payment::where('booking_id', $bookingdata->id)->first();
            if ($paymentData) {
                $paymentData->payment_status = 'Cancelled';
                $paymentData->update();
            }
        }
        // If booking is cancelled and not an advance refund case, mark payment as 'cancelled' when appropriate
        if ($data['status'] === 'cancelled') {
            if ($paymentdata && $paymentdata->payment_status === 'pending') {
                $paymentdata->payment_status = 'cancelled';
                $paymentdata->save();
            }
        }

        $data['reason'] = isset($data['reason']) ? $data['reason'] : null;

        // Cancellation charges: not applied to wallet – cancel happens before advance payment.
        if(!empty($request->extra_charges)){
            if($bookingdata->bookingExtraCharge()->count() > 0)
            {
                $bookingdata->bookingExtraCharge()->delete();
            }

            foreach ($request->extra_charges as $extra) {
                $bookingdata->bookingExtraCharge()->create([
                    'title'      => $extra['title'],
                    'price'      => $extra['price'],
                    'qty'        => $extra['qty'],
                ]);
            }
//            foreach($request->extra_charges as $extra) {
//                $extra_charge = [
//                    'title'   => $extra['title'],
//                    'price'   => $extra['price'],
//                    'qty'   => $extra['qty'],
//                    'booking_id'   =>$bookingdata->id,
//                ];
//                foreach ($request->extra_charges as $extra) {
//                    $bookingdata->bookingExtraCharge()->create([
//                        'title'      => $extra['title'],
//                        'price'      => $extra['price'],
//                        'qty'        => $extra['qty'],
//                    ]);
//                }
//
//            }
            $subtotal = $bookingdata->getSubTotalValue() + $bookingdata->getServiceAddonValue() + $bookingdata->getExtraChargeValue();

            // without include extrachage tax caculation
            $data['final_sub_total'] = $subtotal;
            $tax = $bookingdata->getTaxesValue();
            $data['final_total_tax'] = round($tax,$digitafter_decimal_point);
            $totalamount =   $subtotal + $tax;
            $data['total_amount'] =round($totalamount,$digitafter_decimal_point);

            // with include extracharge tax caculation
            // $totalamount =   $subtotal + $bookingdata->getExtraChargeValue() + $tax;
            // $data['total_amount'] =round($totalamount,2);
            // $data['final_total_tax'] = round($tax,2);
        }


        $bookingdata->update($data);
        // if($bookingdata && $bookingdata->status === 'completed'){
        //     $this->addBookingCommission($bookingdata);
        // }
        //$this->addBookingCommission($bookingdata);

        // Send notification if status changed and activity_type is set
        if($old_status != $data['status']){
            // Reload booking to get fresh data after update with all relationships
            $bookingdata->refresh();
            $bookingdata->load(['customer', 'provider', 'service', 'handymanAdded.handyman', 'payment']);
            $bookingdata->old_status = $old_status;
            
            // Ensure activity_type is set
            if(empty($activity_type)) {
                if($data['status'] == 'cancelled'){
                    $activity_type = 'cancel_booking';
                } else {
                    $activity_type = 'update_booking_status';
                }
            }
            
            $activity_data = [
                'activity_type' => $activity_type,
                'booking_id' => $id,
                'booking' => $bookingdata,
            ];
            
            // Log for debugging
            \Log::info('Sending booking notification', [
                'old_status' => $old_status,
                'new_status' => $data['status'],
                'activity_type' => $activity_type,
                'booking_id' => $id,
                'has_handyman' => $bookingdata->handymanAdded ? $bookingdata->handymanAdded->count() : 0,
                'provider_id' => $bookingdata->provider_id,
                'customer_id' => $bookingdata->customer_id
            ]);
            
            try {
                $this->sendNotification($activity_data);
                \Log::info('Booking notification sent successfully', [
                    'booking_id' => $id,
                    'activity_type' => $activity_type
                ]);
                
                // Ensure database notification is created immediately (fallback if queue fails)
                $this->createDirectDatabaseNotification($bookingdata, $activity_type, $old_status, $data['status']);
            } catch (\Exception $e) {
                \Log::error('Failed to send booking notification: ' . $e->getMessage(), [
                    'booking_id' => $id,
                    'activity_type' => $activity_type,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Fallback: create direct database notification even if sendNotification fails
                try {
                    $this->createDirectDatabaseNotification($bookingdata, $activity_type, $old_status, $data['status']);
                } catch (\Exception $fallbackError) {
                    \Log::error('Failed to create fallback database notification: ' . $fallbackError->getMessage());
                }
            }
            
            // ✅ Send direct email notifications to relevant parties
            try {
                $actor = auth()->user();
                $actorName = $actor ? ($actor->display_name ?? $actor->first_name ?? 'System') : 'System';
                $actorType = 'system';
                
                if ($actor) {
                    if ($actor->hasAnyRole(['provider']) && $actor->id == $bookingdata->provider_id) {
                        $actorType = 'provider';
                    } elseif ($actor->hasAnyRole(['handyman'])) {
                        $actorType = 'handyman';
                    } elseif ($actor->hasAnyRole(['user']) && $actor->id == $bookingdata->customer_id) {
                        $actorType = 'user';
                    }
                }
                
                $newStatus = $data['status'];
                $oldStatusLabel = ucwords(str_replace('_', ' ', $old_status));
                $newStatusLabel = ucwords(str_replace('_', ' ', $newStatus));
                
                // Determine who should receive emails based on who performed the action
                $emailsToSend = [];
                
                if ($actorType === 'handyman') {
                    // Handyman action: notify provider and user
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                } elseif ($actorType === 'provider') {
                    // Provider action: notify user and handyman
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                } elseif ($actorType === 'user') {
                    // User action: notify provider and handyman
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                } else {
                    // System/admin action: notify all parties
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                }
                
                // Send emails to all recipients
                foreach ($emailsToSend as $emailData) {
                    try {
                        Mail::to($emailData['user']->email)->send(
                            new BookingStatusUpdateMail(
                                $emailData['user'],
                                $bookingdata,
                                $old_status,
                                $newStatus,
                                $actorName,
                                $actorType,
                                $emailData['type'] // recipient type: 'provider', 'handyman', or 'user'
                            )
                        );
                        \Log::info('Booking status update email sent', [
                            'booking_id' => $id,
                            'recipient' => $emailData['user']->email,
                            'recipient_type' => $emailData['type'],
                            'old_status' => $old_status,
                            'new_status' => $newStatus,
                            'actor' => $actorName,
                            'actor_type' => $actorType
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send booking status update email', [
                            'booking_id' => $id,
                            'recipient' => $emailData['user']->email ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send booking status update emails: ' . $e->getMessage(), [
                    'booking_id' => $id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if($bookingdata->payment_id != null){
//            $payment_status = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
//            $paymentdata->update(['payment_status' => $payment_status]);
        }

        if($data['status'] == 'completed' && $data['payment_status'] == 'pending_by_admin'){
            $handyman = BookingHandymanMapping::where('booking_id',$bookingdata->id)->first();
            $user = User::where('id',$handyman->handyman_id)->first();
            $payment_history = [
                'payment_id' => $paymentdata->id,
                'booking_id' => $paymentdata->booking_id,
                'type' => $paymentdata->payment_type,
                'sender_id' => $bookingdata->customer_id,
                'receiver_id' => $handyman->handyman_id,
                'total_amount' => $paymentdata->total_amount,
                'datetime' => date('Y-m-d H:i:s'),
                'text' =>  __('messages.payment_transfer',['from' => get_user_name($bookingdata->customer_id),'to' => get_user_name($handyman->handyman_id),
                'amount' => getPriceFormat((float)$paymentdata->total_amount) ]),
            ];
            if($user->user_type == 'provider'){
                $payment_history['status'] = config('constant.PAYMENT_HISTORY_STATUS.APPROVED_PROVIDER');
                $payment_history['action']= config('constant.PAYMENT_HISTORY_ACTION.PROVIDER_APPROVED_CASH');
            }else{
                $payment_history['status'] = config('constant.PAYMENT_HISTORY_STATUS.APPRVOED_HANDYMAN');
                $payment_history['action'] = config('constant.PAYMENT_HISTORY_ACTION.HANDYMAN_APPROVED_CASH');
            }
            if(!empty($paymentdata->txn_id)){
                $payment_history['txn_id'] =$paymentdata->txn_id;
            }
            if(!empty($paymentdata->other_transaction_detail)){
                $payment_history['other_transaction_detail'] =$paymentdata->other_transaction_detail;
            }
           $res =  PaymentHistory::create($payment_history);
           $res->parent_id = $res->id;
           $res->update();
        }
        $message = __('messages.update_form',[ 'form' => __('messages.booking') ] );

        if($request->is('api/*')) {
            return comman_message_response($message);
		}
    }

    public function saveHandymanRating(Request $request)
    {
        $user = auth()->user();
        $rating_data = $request->all();
        $rating_data['customer_id'] = $user->id;
        $result = HandymanRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('messages.update_form',[ 'form' => __('messages.rating') ] );
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.rating') ] );
		}

        return comman_message_response($message);
    }

    public function saveCustomerRating(Request $request)
    {
        $user = auth()->user();
        
        // Validate required fields
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'customer_id' => 'required|integer|exists:users,id',
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string',
            'provider_id' => 'nullable|integer|exists:users,id',
        ]);

        // Get provider_id from request or authenticated user
        $provider_id = $request->provider_id ?? $user->id;
        
        // Verify that the authenticated user is the provider for this booking
        $booking = Booking::find($request->booking_id);
        if (!$booking) {
            return comman_message_response(__('messages.booking_not_found'), 404);
        }
        
        // Verify provider matches the booking's provider
        if ($booking->provider_id != $provider_id && !$user->hasRole(['admin', 'demo_admin'])) {
            return comman_message_response(__('messages.demo_permission_denied'), 403);
        }

        // Verify customer matches the booking's customer
        if ($booking->customer_id != $request->customer_id) {
            return comman_message_response(__('messages.invalid_customer'), 400);
        }

        $rating_data = [
            'booking_id' => $request->booking_id,
            'customer_id' => $request->customer_id,
            'provider_id' => $provider_id,
            'rating' => $request->rating,
            'review' => $request->review ?? null,
        ];

        // Use updateOrCreate to allow updating existing ratings
        $result = CustomerRating::updateOrCreate(
            [
                'booking_id' => $request->booking_id,
                'customer_id' => $request->customer_id,
                'provider_id' => $provider_id,
            ],
            $rating_data
        );

        $message = __('messages.update_form', [ 'form' => __('messages.rating') ]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', [ 'form' => __('messages.rating') ]);
        }

        return comman_message_response($message);
    }

    public function getCustomerRatingInfo(Request $request)
    {
        $customer_id = $request->customer_id ?? $request->rated;
        $customer_id = $customer_id ? (int) $customer_id : null;

        if (!$customer_id) {
            return comman_message_response(__('messages.customer_id_required'), 400);
        }

        // Get customer info
        $customer = User::find($customer_id);
        if (!$customer) {
            return comman_message_response(__('messages.customer_not_found'), 404);
        }

        // Get customer ratings from customer_ratings (booking) — customer_id = customer being rated
        $customerRatings = CustomerRating::where('customer_id', $customer_id)
            ->publicVisible()
            ->with(['provider', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get customer ratings from post_job_bid_ratings — customer rates provider on post job; row still keyed by customer_id
        $postJobBidRatings = PostJobBidRating::where('customer_id', $customer_id)
            ->publicVisible()
            ->with(['provider'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Map both to same format and merge
        $allReviews = $customerRatings->map(function ($rating) {
            return [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'provider_name' => optional($rating->provider)->display_name,
                'created_at' => $rating->created_at ? $rating->created_at->format('Y-m-d') : null,
                'sort_at' => $rating->created_at,
                'status' => (int) ($rating->status ?? CustomerRating::STATUS_VISIBLE),
            ];
        })->concat($postJobBidRatings->map(function ($rating) {
            return [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'provider_name' => optional($rating->provider)->display_name,
                'created_at' => $rating->created_at ? $rating->created_at->format('Y-m-d') : null,
                'sort_at' => $rating->created_at,
                'status' => (int) ($rating->status ?? PostJobBidRating::STATUS_VISIBLE),
            ];
        }))->sortByDesc('sort_at')->values();
        
        // Calculate average rating and total from merged set
        $totalReviews = $allReviews->count();
        $averageRating = $totalReviews > 0 ? $allReviews->avg('rating') : 0;
        
        // Get recent reviews (last 5) - same JSON shape
        $recentReviews = $allReviews->take(5)->map(function ($item) {
            return [
                'id' => $item['id'],
                'rating' => $item['rating'],
                'review' => $item['review'],
                'provider_name' => $item['provider_name'],
                'created_at' => $item['created_at'],
                'status' => $item['status'],
            ];
        })->values();
        
        $response = [
            'customer_id' => $customer_id,
            'customer_name' => $customer->display_name,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'recent_reviews' => $recentReviews
        ];
        
        return comman_custom_response($response);
    }

    public function getHandymanRatingList(Request $request){

        $handymanratings = HandymanRating::query();
        if (! auth()->user()?->hasAnyRole(['admin', 'demo_admin'])) {
            $handymanratings->publicVisible();
        }
        $handymanratings = $handymanratings->orderBy('id', 'desc');

        $per_page = config('constant.PER_PAGE_LIMIT');
        if($request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all'){
                $per_page = $handymanratings->count();
            }
        }

        $handymanratings = $handymanratings->paginate($per_page);
        $data = HandymanRatingResource::collection($handymanratings);

        return response ([
            'pagination' => [
                'total_ratings' => $data->total(),
                'per_page' => $data->perPage(5),
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
            ],
            'data' => $data,
        ]);
    }

    public function deleteHandymanRating(Request $request)
    {
        $user = auth()->user();

        $book_rating = HandymanRating::where('id',$request->id)->where('customer_id',$user->id)->delete();

        $message = __('messages.delete_form',[ 'form' => __('messages.rating') ] );

        return comman_message_response($message);
    }
    public function bookingRatingByCustomer(Request $request){
        $customer_review = null;
        if($request->customer_id != null){
            $customer_review = BookingRating::where('customer_id',$request->customer_id)->where('service_id',$request->service_id)->where('booking_id',$request->booking_id)->first();
            if (!empty($customer_review))
            {
                $customer_review = new BookingRatingResource($customer_review);
            }
        }
        return comman_custom_response($customer_review);

    }
    public function uploadServiceProof(Request $request){
        $booking = $request->all();
        $result = ServiceProof::create($booking);
        if($request->has('attachment_count')) {
            for($i = 0 ; $i < $request->attachment_count ; $i++){
                $attachment = "booking_attachment_".$i;
                if($request->$attachment != null){
                    $file[] = $request->$attachment;
                }
            }
            storeMediaFile($result,$file, 'booking_attachment');
        }
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.attachments') ] );
		}
        return comman_message_response($message);
    }

    public function getUserRatings(Request $request){
        $user = auth()->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Ratings OF the customer (received): from customer_ratings and post_job_bid_ratings (provider rates customer, customer_id = customer being rated)
        $customer_id = $request->customer_id ?? $user->id;
        $customer_id = (int) $customer_id;

        // Non-admin may only view their own customer ratings
        if (!$user->hasRole('admin') && !$user->hasRole('demo_admin') && (int) $user->id !== $customer_id) {
            $customer_id = (int) $user->id;
        }

        $showHidden = $user->hasAnyRole(['admin', 'demo_admin']) || $request->boolean('show_hidden');

        $customerRatingQuery = CustomerRating::where('customer_id', $customer_id)
            ->with(['provider', 'customer'])
            ->orderBy('created_at', 'desc');
        if (!$showHidden) {
            $customerRatingQuery->publicVisible();
        }
        $customerRatingInfo = $customerRatingQuery->get();

        $postJobBidQuery = PostJobBidRating::where('customer_id', $customer_id)
            ->with(['provider', 'customer'])
            ->orderBy('created_at', 'desc');
        if (!$showHidden) {
            $postJobBidQuery->publicVisible();
        }
        $postJobBidRatings = $postJobBidQuery->get();

        $customer = User::find($customer_id);
        $mapItem = function ($r) use ($customer) {
            $bookingId = $r->booking_id ?? null;
            $cust = $customer ?? $r->customer ?? null;
            return [
                'id' => $r->id,
                'booking_id' => $bookingId,
                'customer_id' => $r->customer_id,
                'customer_name' => optional($cust)->display_name,
                'customer_profile_image' => optional($cust)->login_type != null ? optional($cust)->social_image : getSingleMedia($cust, 'profile_image', null),
                'provider_id' => $r->provider_id,
                'provider_name' => optional($r->provider)->display_name,
                'provider_profile_image' => optional($r->provider)->login_type != null ? optional($r->provider)->social_image : getSingleMedia($r->provider, 'profile_image', null),
                'rating' => $r->rating,
                'review' => $r->review,
                'status' => (int) ($r->status ?? CustomerRating::STATUS_VISIBLE),
                'created_at' => $r->created_at ? date('Y-m-d', strtotime($r->created_at)) : null,
                '_sort_at' => $r->created_at,
            ];
        };
        $allReviews = $customerRatingInfo->map(function ($r) use ($mapItem) {
            $item = $mapItem($r);
            $item['booking_id'] = $r->booking_id;
            return $item;
        })->concat($postJobBidRatings->map(function ($r) use ($mapItem) {
            $item = $mapItem($r);
            $item['booking_id'] = null;
            return $item;
        }))->sortByDesc('_sort_at')->values();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = (int) $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $allReviews->count();
            }
        }
        $per_page = max(1, $per_page);
        $page = max(1, (int) $request->get('page', 1));
        $total = $allReviews->count();
        $slice = $allReviews->slice(($page - 1) * $per_page, $per_page)->values();
        $dataPaginated = $slice->map(function ($item) {
            unset($item['_sort_at']);
            return $item;
        });

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $dataPaginated,
            $total,
            $per_page,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response([
            'pagination' => [
                'total_ratings' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'totalPages' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'next_page' => $paginator->nextPageUrl(),
                'previous_page' => $paginator->previousPageUrl(),
            ],
            'data' => $dataPaginated,
        ]);
    }
    public function getRatingsList(Request $request){
        $type = $request->type;

        if ($type === 'user_service_rating') {
            $user = auth()->user();

            if(auth()->user() !== null){

                if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')){
                    $ratings = BookingRating::orderBy('id','desc');
                }
                else{
                    $ratings = BookingRating::where('customer_id', $user->id)->orderBy('id','desc');
                }
            }
        }elseif ($type === 'handyman_rating') {
                $ratings = HandymanRating::query();
                if (! auth()->user()?->hasAnyRole(['admin', 'demo_admin'])) {
                    $ratings->publicVisible();
                }
                $ratings = $ratings->orderBy('id', 'desc');
        }else {
                return response()->json(['message' => 'Invalid type parameter'], 400);
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if($request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all'){
                $per_page = $ratings->count();
            }
        }

        $ratings = $ratings->paginate($per_page);
        $data = HandymanRatingResource::collection($ratings);

        return response ([
            'pagination' => [
                'total_ratings' => $data->total(),
                'per_page' => $data->perPage(5),
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
            ],
            'data' => $data,
        ]);
    }
    public function deleteRatingsList($id,Request $request){
        $type = $request->type;

        if(demoUserPermission()){
            $message = __('messages.demo.permission.denied');
            return comman_message_response($message);
        }
        if ($type === 'user_service_rating') {
            $bookingrating = BookingRating::find($id);
            $msg= __('messages.msg_fail_to_delete',['name' => __('messages.user_ratings')] );

            if($bookingrating != ''){
                $bookingrating->delete();
                $msg= __('messages.msg_deleted',['name' => __('messages.user_ratings')] );
            }
        }elseif ($type === 'handyman_rating') {
            $handymanrating = HandymanRating::find($id);
            $msg= __('messages.msg_fail_to_delete',['name' => __('messages.handyman_ratings')] );

            if($handymanrating != ''){
                $handymanrating->delete();
                $msg= __('messages.msg_deleted',['name' => __('messages.handyman_ratings')] );
            }
        }else {
            $msg = "Invalid type parameter";
            return comman_custom_response(['message'=> $msg, 'status' => false]);
        }

        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

    public function updateLocation(Request $request) {
        $bookingID = $request->input('booking_id');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');


        $data = [
            'booking_id' => $bookingID,
            'latitude' => $latitude,
            'longitude' => $longitude,

        ];
        $locations = LiveLocation::updateOrCreate(['booking_id' => $data['booking_id']], $data);
        $time_zone=getTimeZone();

        $datetime_in_timezone = Carbon::parse($locations->updated_at)->timezone($time_zone);

        $data['datetime'] = $datetime_in_timezone->toDateTimeString();

        $message = __('messages.location_update');
        return response()->json(['data' => $data, 'message' => $message], 200);
    }

    public function getLocation(Request $request){
        $bookingID = $request->input('booking_id');

        $latestLiveLocation = Cache::remember('latest_live_location_' . $bookingID, 30, function () use ($bookingID) {
            return LiveLocation::where('booking_id', $bookingID)
                ->latest()
                ->first();
        });
        if (!$latestLiveLocation) {
            return response()->json(['error' => 'Live location not found for this booking ID'], 404);
        }

        $time_zone=getTimeZone();

        $datetime_in_timezone = Carbon::parse($latestLiveLocation->updated_at)->timezone($time_zone);

        $datetime= $datetime_in_timezone->toDateTimeString();
        $data = [
            'latitude' => $latestLiveLocation->latitude,
            'longitude' => $latestLiveLocation->longitude,
            'datetime' =>  $datetime,
        ];

        $message = __('messages.location_update');
        return response()->json(['data' => $data, 'message' => $message], 200);

    }

    // public function getEarningsBreakdown(Request $request)
    // {
    //     $auth_user = auth()->user();
    //     $bookings = Booking::query()->with('commissionsdata', 'payment', 'handymanAdded');

    //     // Apply filters from the request
    //     if ($request->has('advanceFilter')) {
    //         $advanceFilter = $request->advanceFilter;

    //         $filters = [
    //             'customer_id' => 'customer_id',
    //             'service_id' => 'service_id',
    //             'provider_id' => 'provider_id',
    //             'handyman_id' => ['handymanAdded', 'handyman_id'],
    //             'booking_status' => 'status',
    //             'payment_status' => ['payment', 'payment_status'],
    //             'payment_type' => ['payment', 'payment_type'],
    //             'date_range' => null, // Special handling for date range
    //         ];

    //         foreach ($filters as $key => $filter) {
    //             if (!empty($advanceFilter[$key])) {
    //                 if ($key === 'date_range') {
    //                     // Special handling for date range filter
    //                     $dates = explode(' to ', $advanceFilter['date_range']);
    //                     if (count($dates) === 2) {
    //                         $bookings->whereDate('date', '>=', $dates[0])
    //                                  ->whereDate('date', '<=', $dates[1]);
    //                     } elseif (count($dates) === 1) {
    //                         $bookings->whereDate('date', $dates[0]);
    //                     }
    //                 } else {
    //                     // Handle filters with `whereIn`
    //                     if (is_array($advanceFilter[$key])) {
    //                         if (is_array($filter)) {
    //                             // Relationship-based filters
    //                             $bookings->whereHas($filter[0], function ($query) use ($filter, $advanceFilter, $key) {
    //                                 $query->whereIn($filter[1], $advanceFilter[$key]);
    //                             });
    //                         } else {
    //                             // Direct `whereIn` filters
    //                             $bookings->whereIn($filter, $advanceFilter[$key]);
    //                         }
    //                     } else {
    //                         // Single value filter
    //                         $bookings->where($filter, $advanceFilter[$key]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // Apply role-based filtering
    //     switch ($auth_user->roles->pluck('name')->first()) {
    //         case 'admin':
    //         case 'demo_admin':
    //             $bookings = $bookings->get();
    //             break;

    //         case 'provider':
    //             $bookings = $bookings->where('provider_id', $auth_user->id)->get();
    //             break;

    //         case 'handyman':
    //             $bookings = $bookings->whereHas('handymanAdded', function ($query) use ($auth_user) {
    //                 $query->where('handyman_id', $auth_user->id);
    //             })->get();
    //             break;

    //         default:
    //             $bookings = collect();
    //             break;
    //     }

    //     // Calculate earnings
    //     $earnings = [
    //         'admin' => 0,
    //         'provider' => 0,
    //         'handyman' => 0,
    //         'tax' => 0,
    //         'discount' => 0,
    //         'total' => 0,
    //         'totalAmountWithDiscount' => 0,
    //         'totalAmountWithoutDiscount' => 0,
    //     ];

    //     if ($bookings->count() > 0) {
    //         foreach ($bookings as $booking) {
    //             foreach ($booking->commissionsdata as $commission) {
    //                 switch ($commission->user_type) {
    //                     case 'admin':
    //                         $earnings['admin'] += $commission->commission_amount;
    //                         break;
    //                     case 'provider':
    //                         $earnings['provider'] += $commission->commission_amount;
    //                         break;
    //                     case 'handyman':
    //                         $earnings['handyman'] += $commission->commission_amount;
    //                         break;
    //                 }
    //             }
    //             $earnings['tax'] += $booking->final_total_tax ?? 0;
    //             $earnings['discount'] += $booking->final_discount_amount ?? 0;
    //             $earnings['totalAmountWithDiscount'] += $booking->total_amount ?? 0;
    //             $earnings['totalAmountWithoutDiscount'] += ($booking->total_amount - $booking->final_discount_amount) ?? 0;
    //             $earnings['total'] += $booking->total_amount ?? 0;
    //         }
    //     }

    //     // Format all numeric values to 2 decimal places
    //     array_walk($earnings, function(&$value) {
    //         $value = number_format($value, 2, '.', '');
    //     });

    //     $response = [
    //         'status' => true,
    //         'totalEarning' => $earnings['total'],
    //         'earnings' => $earnings,
    //         'userRole' => $auth_user->roles->pluck('name')->first()
    //     ];

    //     return comman_custom_response($response);
    // }

    /**
     * Get booking ratings list for handyman
     * Returns ratings for bookings where handyman was involved
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingRatingsList(Request $request)
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // For handyman, get ratings for bookings where they were assigned
        if ($user->hasRole('handyman')) {
            $query = BookingRating::query()
                ->with([
                    'customer.country',
                    'customer.city',
                    'service',
                    'booking.handymanAdded' => function($q) use ($user) {
                        $q->where('handyman_id', $user->id);
                    }
                ])
                ->whereHas('booking.handymanAdded', function($q) use ($user) {
                    $q->where('handyman_id', $user->id);
                });
        } else {
            // For other roles, use the myRating scope
            $query = BookingRating::query()->myRating()
                ->with(['customer.country', 'customer.city', 'service']);
        }

        $this->applyReviewStatusListFilter($query, $request, BookingRating::class);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('display_name', 'like', '%' . $search . '%')
                                  ->orWhere('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('service', function ($serviceQuery) use ($search) {
                    $serviceQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('review', 'like', '%' . $search . '%');
            });
        }

        // Pagination
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $query->count();
            }
        }

        // Ordering
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $ratings = $query->paginate($per_page);

        // Transform data
        $transformedData = $ratings->getCollection()->map(function ($rating) {
            $customer = $rating->customer;
            $service = $rating->service;
            
            return [
                'id' => $rating->id,
                'booking_id' => $rating->booking_id,
                'customer' => [
                    'id' => $customer->id ?? null,
                    'name' => $customer->display_name ?? ($customer ? ($customer->first_name . ' ' . $customer->last_name) : '-'),
                    'profile_image' => getSingleMedia($customer, 'profile_image', null),
                    'address' => ($customer->country->name ?? '--') . '-' . ($customer->city->name ?? '--'),
                ],
                'service' => [
                    'id' => $service->id ?? null,
                    'name' => $service->name ?? '-',
                ],
                'rating' => (float)$rating->rating,
                'review' => $rating->review ?? '-',
                'status' => (int) ($rating->status ?? BookingRating::STATUS_VISIBLE),
                'created_at' => $rating->created_at ? $rating->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $rating->updated_at ? $rating->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $response = [
            'pagination' => [
                'total_items' => $ratings->total(),
                'per_page' => $ratings->perPage(),
                'current_page' => $ratings->currentPage(),
                'total_pages' => $ratings->lastPage(),
                'from' => $ratings->firstItem(),
                'to' => $ratings->lastItem(),
                'next_page_url' => $ratings->nextPageUrl(),
                'previous_page_url' => $ratings->previousPageUrl(),
            ],
            'data' => $transformedData,
        ];

        return comman_custom_response($response);
    }

    /**
     * Get handyman ratings list
     * Returns ratings given to handyman by customers
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHandymanRatingsList(Request $request)
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Use myRating scope which filters by handyman_id for handyman role
        $query = HandymanRating::query()->myRating()
            ->with([
                'handyman.country',
                'handyman.city',
                'customer.country',
                'customer.city',
                'service',
                'booking'
            ]);

        $this->applyReviewStatusListFilter($query, $request, HandymanRating::class);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('display_name', 'like', '%' . $search . '%')
                                  ->orWhere('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('handyman', function ($handymanQuery) use ($search) {
                    $handymanQuery->where('display_name', 'like', '%' . $search . '%')
                                  ->orWhere('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('service', function ($serviceQuery) use ($search) {
                    $serviceQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('review', 'like', '%' . $search . '%');
            });
        }

        // Pagination
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $query->count();
            }
        }

        // Ordering
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $ratings = $query->paginate($per_page);

        // Transform data
        $transformedData = $ratings->getCollection()->map(function ($rating) {
            $handyman = $rating->handyman;
            $customer = $rating->customer;
            $service = $rating->service;
            
            return [
                'id' => $rating->id,
                'booking_id' => $rating->booking_id,
                'handyman' => [
                    'id' => $handyman->id ?? null,
                    'name' => $handyman->display_name ?? ($handyman ? ($handyman->first_name . ' ' . $handyman->last_name) : '-'),
                    'profile_image' => getSingleMedia($handyman, 'profile_image', null),
                    'address' => ($handyman->country->name ?? '--') . '-' . ($handyman->city->name ?? '--'),
                ],
                'customer' => [
                    'id' => $customer->id ?? null,
                    'name' => $customer->display_name ?? ($customer ? ($customer->first_name . ' ' . $customer->last_name) : '-'),
                    'profile_image' => getSingleMedia($customer, 'profile_image', null),
                    'address' => ($customer->country->name ?? '--') . '-' . ($customer->city->name ?? '--'),
                ],
                'service' => [
                    'id' => $service->id ?? null,
                    'name' => $service->name ?? '-',
                ],
                'rating' => (float)$rating->rating,
                'review' => $rating->review ?? '-',
                'status' => (int) ($rating->status ?? HandymanRating::STATUS_VISIBLE),
                'created_at' => $rating->created_at ? $rating->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $rating->updated_at ? $rating->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $response = [
            'pagination' => [
                'total_items' => $ratings->total(),
                'per_page' => $ratings->perPage(),
                'current_page' => $ratings->currentPage(),
                'total_pages' => $ratings->lastPage(),
                'from' => $ratings->firstItem(),
                'to' => $ratings->lastItem(),
                'next_page_url' => $ratings->nextPageUrl(),
                'previous_page_url' => $ratings->previousPageUrl(),
            ],
            'data' => $transformedData,
        ];

        return comman_custom_response($response);
    }

    /**
     * Default: only public reviews (status 0 / null). Admins or show_hidden=1 see all.
     * filter[column_status]: 0 = visible (+null); 1 = hidden only (blocked unless admin/show_hidden).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function applyReviewStatusListFilter($query, Request $request, string $modelClass): void
    {
        $user = auth()->user();
        $showHidden = $user && ($user->hasAnyRole(['admin', 'demo_admin']) || $request->boolean('show_hidden'));

        $filter = $request->input('filter', []);
        if (! is_array($filter)) {
            $filter = [];
        }

        $visible = $modelClass::STATUS_VISIBLE;
        $hasStatusFilter = array_key_exists('column_status', $filter)
            && $filter['column_status'] !== ''
            && $filter['column_status'] !== null;

        if ($hasStatusFilter) {
            $col = (int) $filter['column_status'];
            if ($col === $visible) {
                $query->where(function ($q) use ($visible) {
                    $q->where('status', $visible)->orWhereNull('status');
                });
            } else {
                $query->where('status', $col);
                if (! $showHidden) {
                    $query->whereRaw('0 = 1');
                }
            }
        } elseif (! $showHidden) {
            $query->publicVisible();
        }
    }

    /**
     * Create direct database notification as fallback
     * This ensures notifications are saved even if queue fails or templates are missing
     */
    private function createDirectDatabaseNotification($booking, $activity_type, $old_status, $new_status)
    {
        try {
            $statusLabel = ucwords(str_replace('_', ' ', $new_status));
            $oldStatusLabel = ucwords(str_replace('_', ' ', $old_status));
            $serviceName = optional($booking->service)->name ?? 'Service';
            $customerName = optional($booking->customer)->display_name ?? optional($booking->customer)->name ?? 'Customer';
            $providerName = optional($booking->provider)->display_name ?? optional($booking->provider)->name ?? 'Provider';
            
            // Create a proper notification message
            $message = "Booking #{$booking->id} for {$serviceName} status has been updated from {$oldStatusLabel} to {$statusLabel}";
            
            $notificationData = [
                'id' => $booking->id,
                'type' => $activity_type,
                'subject' => 'Booking Status Updated',
                'booking_id' => $booking->id,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'status_label' => $statusLabel,
                'service_name' => $serviceName,
                'customer_name' => $customerName,
                'provider_name' => $providerName,
                'message' => $message,
                'notification-type' => 'booking',
                'created_at' => now()->toDateTimeString(),
            ];

            // Notify customer
            if ($booking->customer_id) {
                \DB::table('notifications')->insert([
                    'id' => \Str::uuid()->toString(),
                    'type' => 'App\Notifications\CommonNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $booking->customer_id,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notify provider
            if ($booking->provider_id) {
                \DB::table('notifications')->insert([
                    'id' => \Str::uuid()->toString(),
                    'type' => 'App\Notifications\CommonNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $booking->provider_id,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notify handymen if any
            if ($booking->handymanAdded && $booking->handymanAdded->count() > 0) {
                foreach ($booking->handymanAdded as $handymanMapping) {
                    if ($handymanMapping->handyman_id) {
                        \DB::table('notifications')->insert([
                            'id' => \Str::uuid()->toString(),
                            'type' => 'App\Notifications\CommonNotification',
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $handymanMapping->handyman_id,
                            'data' => json_encode($notificationData),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create direct database notification: ' . $e->getMessage());
        }
    }
}
