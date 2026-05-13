<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Payment;

use App\Models\City;
use App\Models\State;
use App\Models\Bank;
use App\Models\ProviderTaxMapping;
use App\Models\ProviderServiceAddressMapping;
use App\Http\Resources\API\ProviderTaxResource;
use App\Models\Service;
use App\Models\ProviderType;
use App\Models\HandymanType;
use App\Models\CouponServiceMapping;
use App\Models\Coupon;
use App\Models\Booking;
use App\Models\Tax;
use App\Models\AppSetting;
use App\Models\User;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\TypeResource;
use App\Http\Resources\API\BankResource;
use App\Http\Resources\API\CouponResource;
use App\Http\Resources\API\TaxResource;
use App\Support\UgcListing;
use PDF;


class CommanController extends Controller
{
    public function getCountryList(Request $request)
    {
        $list = Country::get();

        return response()->json( $list );
    }

    public function getStateList(Request $request)
    {
        $list = State::where('country_id',$request->country_id)->get();

        return response()->json( $list );
    }

    public function getCityList(Request $request)
    {
        $list = City::where('state_id',$request->state_id)->get();

        return response()->json( $list );
    }

    /**
     * Spoken language choices for profile (provider / handyman / user).
     * Same source as `config('spoken_language_options')` in profile forms — `code` is what you store in `users.languages`.
     */
    public function getSpokenLanguageOptions()
    {
        $map = config('spoken_language_options', []);
        $data = collect($map)->map(function (string $name, string $code) {
            return [
                'code' => $code,
                'name' => $name,
            ];
        })->values()->all();

        return comman_custom_response([
            'data' => $data,
            'options' => $map,
        ]);
    }

    public function getProviderTax(Request $request){

        $provider_id  = !empty($request->provider_id) ? $request->provider_id : auth()->user()->id;
        $taxes = ProviderTaxMapping::with('taxes');

        if(auth()->user() !== null){
            if(auth()->user()->hasRole('admin')){
                $taxes = $taxes;
            }
        else{
            $taxes = $taxes->where('provider_id',$provider_id)->whereHas('taxes', function ($a)  {
                $a->where('status', 1);
            });
          }
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $taxes->count();
            }
        }
        $taxes = $taxes->orderBy('created_at','desc')->paginate($per_page);
        $items = ProviderTaxResource::collection($taxes);

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
   public function getSearchList(Request $request){
        $providerProfileComplete = 0;
        $providerIds = [];
        if($request->has('provider_id') && $request->provider_id != '' ){
            $providerIds = array_filter(explode(',',$request->provider_id), function($v){
                return $v !== '' && $v !== 'null' && $v !== null;
            });
        } elseif (auth()->check() && auth()->user()->user_type === 'provider') {
            $providerIds = [auth()->id()];
        }

        if(!empty($providerIds)){
            $providerProfileComplete = (int) optional(User::whereIn('id', $providerIds)->withTrashed()->first())->profile_complete;
        }

        $service = Service::where('status',1)
            ->where('service_type','service')->where('is_hidden_from_public',0)
            ->with([
                'providers',
                'providers.city',
                'providers.country',
                'category',
                'serviceRatingPublic',
                'city',
                'country'
            ])->orderBy('created_at','desc');
        if(!empty($providerIds)){
            $service->whereIn('provider_id',$providerIds);
        }
        
        // Check if subcategory_id is provided and valid
        $hasValidSubcategory = false;
        if($request->has('subcategory_id') && $request->subcategory_id != '' && $request->subcategory_id != 'null' && $request->subcategory_id != null){
            $subcategoryIds = array_filter(explode(',', $request->subcategory_id), function($v){ 
                return $v !== '' && $v !== 'null' && $v !== null; 
            });
            if(!empty($subcategoryIds)){
                // When subcategory_id is provided, filter strictly by subcategory (takes precedence over category_id)
                // This ensures only services from the specific subcategory are returned
                $service->whereIn('subcategory_id', $subcategoryIds);
                $hasValidSubcategory = true;
            }
        }
        
        // Only apply category filter if subcategory_id is NOT provided
        // When subcategory_id is provided, it already ensures the correct category (since subcategories belong to categories)
        if(!$hasValidSubcategory && $request->has('category_id') && $request->category_id != ''){
            $service->whereIn('category_id',explode(',',$request->category_id));
        }
        // Location-based filters: match Service location OR Provider location
        if($request->has('country_id') && $request->country_id != ''){
            $countryIds = array_filter(explode(',', $request->country_id), function($v){ return $v !== ''; });
            $service->where(function($q) use ($countryIds) {
                $q->whereIn('country_id', $countryIds)
                  ->orWhereHas('providers', function($p) use ($countryIds) {
                      $p->whereIn('country_id', $countryIds);
                  });
            });
        }
        if($request->has('state_id') && $request->state_id != ''){
            $stateIds = array_filter(explode(',', $request->state_id), function($v){ return $v !== ''; });
            $service->where(function($q) use ($stateIds) {
                $q->whereIn('state_id', $stateIds)
                  ->orWhereHas('providers', function($p) use ($stateIds) {
                      $p->whereIn('state_id', $stateIds);
                  });
            });
        }
        if($request->has('city_id') && $request->city_id != ''){
            $cityIds = array_filter(explode(',', $request->city_id), function($v){ return $v !== ''; });
            $service->where(function($q) use ($cityIds) {
                $q->whereIn('city_id', $cityIds)
                  ->orWhereHas('providers', function($p) use ($cityIds) {
                      $p->whereIn('city_id', $cityIds);
                  });
            });
        }
        if($request->has('is_price_min') && $request->is_price_min != '' || $request->has('is_price_max') && $request->is_price_max != ''){
            $service->whereBetween('price', [$request->is_price_min, $request->is_price_max]);
        }
        if($request->has('search')){
            $service->where('name','like',"%{$request->search}%");
        }
        if($request->has('is_featured')){
            $service->where('is_featured',$request->is_featured);
        }
        if($request->has('type')){
            $service->where('type',$request->type);
        }
        if (default_earning_type() === 'subscription') {
            $service->whereHas('providers', function ($a) {
                $a->where('status', 1)
                    ->whereNull('deleted_at')
                    ->where('user_type', 'provider')
                    ->where('is_subscribe', 1);
            });
        }
        UgcListing::scopePublicServices($service, auth()?->id());
        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $get_distance = getSettingKeyValue('site-setup','radious');
            $get_unit = getSettingKeyValue('site-setup','distance_type');

            $locations = $service->locationService($request->latitude,$request->longitude,$get_distance,$get_unit);
            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id',$locations)->get()->pluck('service_id');
            $service->with('providerServiceAddress')->whereIn('id',$service_in_location);
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $service->count();
            }
        }

        if ($request->has('is_rating') && $request->is_rating != '') {
            $isRatings = array_map('floatval', explode(',', $request->is_rating));

            $service->whereHas('serviceRatingPublic', function ($q) use ($isRatings) {
                $conditions = implode(' OR ', array_fill(0, count($isRatings), '(AVG(rating) >= ? AND AVG(rating) <= ?)'));

                $q->select('service_id', \DB::raw('AVG(rating) as average_rating'))
                    ->groupBy('service_id')
                    ->havingRaw($conditions, array_reduce($isRatings, function ($carry, $item) {
                        return array_merge($carry, [$item, $item + 0.9]);
                    }, []));
            });
        }

        $service = $service->where('status',1)->paginate($per_page);

        $items = ServiceResource::collection($service);
        $userservices  = null;
        if($request->customer_id != null){
            $user_service = Service::where('status',1)->where('added_by',$request->customer_id)->get();
            $userservices = ServiceResource::collection($user_service);
        }
        $maxprice = (int) round($service->max('price'));
        $minprice = (int) round($service->min('price'));
        $response = [
            'data' => $items,
            'max'=> $maxprice,
            'min'=> $minprice,
            'userservices' => $userservices,
            'provider_profile_complete' => $providerProfileComplete,
        ];

        return comman_custom_response($response);
    }

    public function getTypeList(Request $request){
        $user_type  = !empty($request->type) ? $request->type : '';
        $provider_id = !empty($request->provider_id) ? $request->provider_id : '';
        $adminIds = \App\Models\User::where('user_type', 'admin')->pluck('id')->toArray();
        if($user_type === 'provider'){
            $typeData = ProviderType::where('status', 1);
        }else{
            $typeData = HandymanType::where('status',1);
            $typeData->whereIn('created_by', array_merge([$provider_id], $adminIds));
        }
        
        if($user_type === 'provider' && $request->is_delete == true){
            $typeData = ProviderType::withTrashed();
        }elseif($user_type === 'handyman' && $request->is_delete == true){
            $typeData = HandymanType::withTrashed();
            $typeData->whereIn('created_by', array_merge([$provider_id], $adminIds));
        }
        if(auth()->user() !== null){
            if(auth()->user()->hasRole('admin')){
                if($user_type === 'provider'){
                    $typeData = ProviderType::withTrashed();
                }else{
                    $typeData = HandymanType::withTrashed();
                    $typeData->whereIn('created_by', array_merge([$provider_id], $adminIds));
                }
            }
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $taxes->count();
            }
        }
        $typeData = $typeData->orderBy('id','desc')->paginate($per_page);
        $items = TypeResource::collection($typeData);
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
    public function getCouponList(Request $request){
        $coupondata = Coupon::withTrashed();
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $taxes->count();
            }
        }
        $coupondata = $coupondata->orderBy('id','desc')->paginate($per_page);
        $items = CouponResource::collection($coupondata);
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
    public function getCouponService(Request $request){
        $servicedata = CouponServiceMapping::where('coupon_id',$request->coupon_id)->withTrashed();
        $service_id = $servicedata->pluck('service_id');
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $taxes->count();
            }
        }
        $service = Service::whereIn('id',$service_id)->orderBy('id','desc')->paginate($per_page);
        $items = ServiceResource::collection($service);
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

        return comman_custom_response($items);
    }
public function downloadInvoice(Request $request){
    $email = $request->email;
    $booking_id = $request->booking_id;

    $bookingdata = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge','bookingPackage')
                    ->where('id', $booking_id)
                    ->first();

    $payment = Payment::where('booking_id', $booking_id)->orderBy('id', 'desc')->first() ?? null;

    $emailData['email'] = $email;
    $emailData['title'] = env('APP_NAME');
    $emailData['body'] = __('messages.invoice_mail_body', ['booking_id' => $booking_id]);

    $data = AppSetting::first();

    $pdf = PDF::loadView('booking.invoice', [
        'bookingdata' => $bookingdata,
        'data' => $data,
        'payment' => $payment
    ]);

    try {
        \Mail::send('booking.invoice_email', $emailData, function($message) use ($data, $pdf, $emailData, $booking_id) {
            $message->to($emailData['email'])
                    ->subject($emailData['title'])
                    ->attachData($pdf->output(), 'invoice_' . $booking_id . '.pdf');
        });

        $messagedata = __('messages.send_invoice');
        return comman_message_response($messagedata);
    } catch (\Throwable $th) {
        $messagedata = __('messages.something_wrong');
        return comman_message_response($messagedata);
    }
}

public function getBankList(Request $request){
    $user = $request->user(); // get authenticated user
    $user_id = $user->id;

    $banks = Bank::where('provider_id', $user_id);
                 

    $per_page = config('constant.PER_PAGE_LIMIT');

    if($request->has('per_page') && !empty($request->per_page)){
        if(is_numeric($request->per_page)){
            $per_page = $request->per_page;
        }
        if($request->per_page === 'all'){
            $per_page = $banks->count();
        }
    }

    $banks = $banks->paginate($per_page);
    $items = BankResource::collection($banks);

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


    public function defaultBank(Request $request)
    {
        $bank_id = $request->id;

        // Check if the bank exists
        $bank = Bank::find($bank_id);

        if ($bank) {
            // Set all banks' is_default column to 0
            Bank::query()->update(['is_default' => 0]);

            // Set the specified bank's is_default column to 1
            $bank->update(['is_default' => 1]);

            $message = 'Bank Default Set Successfully';
            return response()->json(['status' => true, 'message' => $message]);
        } else {
            $message = 'Bank not found';
            return response()->json(['status' => false, 'message' => $message]);
        }
    }    
    public function getTaxList(Request $request){

        $taxes = new Tax();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $taxes->count();
            }
        }
        $taxes = $taxes->orderBy('created_at','desc')->paginate($per_page);
        $items = TaxResource::collection($taxes);

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
}
