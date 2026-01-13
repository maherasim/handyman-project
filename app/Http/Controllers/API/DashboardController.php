<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\{
    Booking,
    Category,
    Service,
    Payment,
    Slider,
    User,
    Setting,
    AppSetting,
    ProviderType,
    ProviderPayout,
    PaymentGateway,
    BookingRating,
    HandymanRating,
    HandymanType,
    HandymanPayout,
    BookingHandymanMapping,
    ProviderServiceAddressMapping,
    Wallet,
    Blog,
    PostJobRequest,
    FrontendSetting,
    Country,
    LiveLocation,
    CommissionEarning,
};
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\API\{
    BookingResource,
    ServiceResource,
    CategoryResource,
    SliderResource,
    UserResource,
    PaymentGatewayResource,
    BookingRatingResource,
    HandymanRatingResource,
    PostJobRequestResource,
    BlogResource,
    CountryResource
};
  
class DashboardController extends Controller
{

    public function dashboardDetail(Request $request){
        $per_page = 6;
        $customer_review = null;
        $notification = 0;
        $upcomming_booking = null;
        $is_email_verified = 0;

        $slider = SliderResource::collection(Slider::where('status',1)->paginate($per_page));

        $category_section = FrontendSetting::getValueByKey('section_2');
        $category= CategoryResource::collection( Category::whereIN( 'id' ,$category_section->category_id )->orderBy('name','asc')->paginate(8));

        $service = Service::with('city', 'country')->where('status',1)->where('service_type','service');
        $service = $service->whereHas('providers', function ($a) use ($request) {
            $a->where('status', 1);
        });
        if ($request->has('city_id') && !empty($request->city_id)) {
            $service = $service->whereHas('providers', function ($a) use ($request) {
                $a->where('city_id', $request->city_id);
            });
        }
        if(default_earning_type() === 'subscription'){
            $service = $service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe',1);
            });
        }
        $servicePaginated = $service->orderBy('id','desc')->paginate($per_page);
        $service = ServiceResource::collection($servicePaginated);
        
        $service = $service->map(function ($item) use ($request) {
            $itemArray = $item->toArray($request);
            $completedBookingCount = Booking::where('service_id', $itemArray['id'])
                ->where('status', 'completed')
                ->count();
            $itemArray['completed_booking_count'] = $completedBookingCount;
            
            // Add provider total services count
            $providerTotalServices = Service::where('provider_id', $itemArray['provider_id'])
                ->where('service_type', 'service')
                ->where('status', 1)
                ->count();
            $itemArray['provider_total_services'] = $providerTotalServices;
            
            return $itemArray;
        });
        

        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $get_distance = getSettingKeyValue('site-setup','radious');
            $get_unit = getSettingKeyValue('site-setup','distance_type');

            $locations = Service::locationService($request->latitude,$request->longitude,$get_distance,$get_unit);
            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id',$locations)->get()->pluck('service_id');
            $service = Service::with('providerServiceAddress', 'city', 'country')->whereIn('id',$service_in_location)->orwhere('visit_type','online')->get();
            $service = ServiceResource::collection($service);
            
            $service = $service->map(function ($item) use ($request) {
                $itemArray = $item->toArray($request);
                $completedBookingCount = Booking::where('service_id', $itemArray['id'])
                    ->where('status', 'completed')
                    ->count();
                $itemArray['completed_booking_count'] = $completedBookingCount;
                
                // Add provider total services count
                $providerTotalServices = Service::where('provider_id', $itemArray['provider_id'])
                    ->where('service_type', 'service')
                    ->where('status', 1)
                    ->count();
                $itemArray['provider_total_services'] = $providerTotalServices;
                
                return $itemArray;
            });
        }

        $provider = User::where('user_type','provider')->where('status',1);

        if(default_earning_type() === 'subscription'){
            $provider = $provider->where('is_subscribe',1);
        }
        if ($request->has('city_id') && !empty($request->city_id)) {
            $provider = $provider->where('city_id', $request->city_id);
        }
        $provider = UserResource::collection($provider->paginate($per_page));

        $featured_service_section = FrontendSetting::getValueByKey('section_4');
        $featured_service= Service::with('city', 'country')->whereIN( 'id' ,$featured_service_section->service_id );
        $featured_service = $featured_service->whereHas('providers', function ($a) use ($request) {
            $a->where('status', 1);
        });
        if(default_earning_type() === 'subscription'){
            $featured_service = $featured_service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe',1);
            });
        }
        $featured_service = ServiceResource::collection($featured_service->orderBy('id','desc')->paginate($per_page));
        
        $featured_service = $featured_service->map(function ($item) use ($request) {
            $itemArray = $item->toArray($request);
            $completedBookingCount = Booking::where('service_id', $itemArray['id'])
                ->where('status', 'completed')
                ->count();
            $itemArray['completed_booking_count'] = $completedBookingCount;
            
            // Add provider total services count
            $providerTotalServices = Service::where('provider_id', $itemArray['provider_id'])
                ->where('service_type', 'service')
                ->where('status', 1)
                ->count();
            $itemArray['provider_total_services'] = $providerTotalServices;
            
            return $itemArray;
        });

        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $get_distance = getSettingKeyValue('site-setup','radious');
            $get_unit = getSettingKeyValue('site-setup','distance_type');

            $locations = Service::locationService($request->latitude,$request->longitude,$get_distance,$get_unit);
            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id',$locations)->get()->pluck('service_id');
            $featured_service = Service::with('providerServiceAddress', 'city', 'country')->whereIn('id',$service_in_location)->where('is_featured',1) ->get();
            $featuredServicePaginated = $featured_service->orderBy('id','desc')->paginate($per_page);
            $featured_service = ServiceResource::collection($featuredServicePaginated);
            
            $featured_service = $featured_service->map(function ($item) use ($request) {
                $itemArray = $item->toArray($request);
                $completedBookingCount = Booking::where('service_id', $itemArray['id'])
                    ->where('status', 'completed')
                    ->count();
                $itemArray['completed_booking_count'] = $completedBookingCount;
                
                // Add provider total services count
                $providerTotalServices = Service::where('provider_id', $itemArray['provider_id'])
                    ->where('service_type', 'service')
                    ->where('status', 1)
                    ->count();
                $itemArray['provider_total_services'] = $providerTotalServices;
                
                return $itemArray;
            });
            
        }

        if($request->has('customer_id') && isset($request->customer_id)){
            $customer_review = BookingRating::with('customer','service')->where('customer_id',$request->customer_id)->get();
            if (!empty($customer_review))
            {
                $customer_review = BookingRatingResource::collection($customer_review);
            }
            $user = User::where('id',$request->customer_id)->first();

            $notification=0;

            if($user){

                $notification = count($user->unreadNotifications);
                $is_email_verified = $user->is_email_verified ? 1 : 0;
            }

            $upcomming_booking = Booking::where('customer_id',$request->customer_id)
            ->with('customer')->where('status', 'accept')->orderBy('id', 'DESC')->first();

            if(!empty($upcomming_booking)){
                $upcomming_booking = New BookingResource($upcomming_booking);
            }


        }

        $blogs = BlogResource::collection(Blog::paginate($per_page));

        $response = [
           'status'         => true,
           'slider'         => $slider,
           'category'       => $category,
           'service'        => $service,
           'featured_service' => $featured_service,
           'provider'       => $provider,
           'customer_review' => $customer_review,
           'notification_unread_count' => $notification,
           'upcomming_confirmed_booking' =>$upcomming_booking,
           'blogs' => $blogs,
           'is_email_verified' => $is_email_verified,
        ];

        return comman_custom_response($response);
    }
    public function providerDashboard(Request $request){
        $per_page = config('constant.PER_PAGE_LIMIT');

        $provider = User::find(auth()->user()->id);

        $booking = Booking::myBooking();
        $total_booking = $booking->count();

        $service = Service::myService()->where('status',1);
        $total_service = $service->count();

        if ($request->has('city_id') && !empty($request->city_id)) {
            $service = $service->whereHas('providers', function ($a) use ($request) {
                $a->where('city_id', $request->city_id);
            });
        }

        $service = ServiceResource::collection($service->orderBy('id','desc')->take(4)->get());

        $total_active_handyman = UserResource::collection(User::myUsers()->where('status', 1)->get());

        $handyman = UserResource::collection(User::myUsers()->where('status', 1)->take(4)->get());

        $total_revenue    = ProviderPayout::where('provider_id',$provider->id)
            ->where('status','paid')
            ->sum('amount') ?? 0;

        $handymanIds = User::with('providerHandyman')
        ->where('provider_id', $provider->id)
        ->pluck('id');
        $handymanIds[] = $provider->id;
        $user = User::with('commission_earning')->where('id', $provider->id)->where('user_type', 'provider')->first();
        $commissions = $user->commission_earning()
        ->whereHas('getbooking', function ($query) {
            $query->where('status', 'completed');
        })
        ->where('commission_status', 'unpaid')
        ->pluck('booking_id'); // Get all booking IDs

        $ProviderEarning = 0;

        if ($commissions->isNotEmpty()) {
            // Fetch all unpaid commissions for the relevant bookings in a single query
            $ProviderEarning = CommissionEarning::whereIn('booking_id', $commissions)
                ->whereIn('user_type', ['provider', 'handyman'])
                ->where('commission_status', 'unpaid')
                ->sum('commission_amount'); // Directly sum the commission_amount
        }
        $remaining_payout  = $ProviderEarning;
        //$remaining_payout  = CommissionEarning::where('employee_id',$provider->id)->where('commission_status', 'unpaid')->sum('commission_amount') ?? 0;
        $revenuedata = ProviderPayout::selectRaw('sum(amount) as total , DATE_FORMAT(updated_at , "%m") as month')
                ->where('provider_id', $user->id)
                ->whereYear('updated_at', date('Y'))
                ->where('status', 'paid')
                // ->whereIn('commission_status', ['paid'])
                ->groupBy('month');
        $revenuedata= $revenuedata->get();
        $data['revenueData']    =    [];
        for($i = 1; $i <= 12; $i++ ){
            $revenueData = 0.0;
            foreach($revenuedata as $revenue){
                if($revenue['month'] == $i){

                    $data['revenueData'][] = [
                        $i => $revenue['total']
                    ];
                    $revenueData++;
                }
            }
            if($revenueData == 0){
                $data['revenueData'][] = (object) [] ;
            }
        }

        $commission = ProviderType::where('id',$provider->providertype_id)->first();

        $notification = count($provider->unreadNotifications);

        $active_plan = get_user_active_plan($provider->id);
        if(is_any_plan_active($provider->id) == 0 && is_subscribed_user($provider->id) == 0 ){
            $active_plan = user_last_plan($provider->id);
        }
        $provider_wallet = Wallet::where('user_id',$provider->id)->where('status',1)->first();
        $online_handyman = User::myUsers()->where('is_available',1)->orderBy('last_online_time','desc')->limit(10)->get();
        $profile_array = [];
        if(!empty($online_handyman)){
            foreach ($online_handyman as $online) {
                $profile_array[] = $online->login_type !== null ? $online->social_image : getSingleMedia($online, 'profile_image',null);
            }
        }
        $post_request = PostJobRequest::where('status','requested')->latest()->take(5)->get();
        $post_requests = PostJobRequestResource::collection($post_request);
        $upcomming_booking = Booking::myBooking()->with('customer')->where('date','>', now())->where('status', 'pending')->orderBy('id', 'DESC')->take(5)->get();
        if(!empty($upcomming_booking)){
            $upcomming_booking = BookingResource::collection($upcomming_booking);
        }

        $response = [
            'status'         => true,
            'total_booking'  => $total_booking,
            'total_service'  => $total_service,
            'total_active_handyman' => $total_active_handyman->count(),
            'total_cash_in_hand'     => total_cash_in_hand(auth()->user()->id),
            'service'        => $service,
            'handyman'       => $handyman,
            'total_revenue'  => $total_revenue,
            'monthly_revenue'=> $data,
            'commission'     => $commission,
            'notification_unread_count' => $notification,
            'subscription'  => $active_plan,
            'is_subscribed' => is_subscribed_user($provider->id),
            'provider_wallet' => $provider_wallet,
            'online_handyman' => $profile_array,
            'post_requests' => $post_requests,
            'upcomming_booking' => $upcomming_booking,
            'is_email_verified' => $provider->is_email_verified,
            'remaining_payout' => $remaining_payout,
         ];

         return comman_custom_response($response);

    }
    public function handymanDashboard(Request $request){
        $per_page = config('constant.PER_PAGE_LIMIT');
        $handyman = User::find(auth()->user()->id);
        if($handyman){
            $sitesetup = Setting::getValueByKey('site-setup','site-setup');
            $get_current_time = Carbon::now();
            $handyman->last_online_time = $get_current_time->toTimeString();
            $handyman->update();
        }
        $booking =  BookingHandymanMapping::with('bookings')->where('handyman_id',auth()->user()->id)->get();

        $upcomming = BookingHandymanMapping::with('bookings')->whereHas('bookings', function($bookings){
            $bookings->where('status','accept');
        })->where('handyman_id',auth()->user()->id)->orderBy('id','DESC')->get();

        $today_booking =  BookingHandymanMapping::with('bookings')->whereHas('bookings', function($bookings){
            $bookings->whereDate('date', Carbon::today());
        })->where('handyman_id',auth()->user()->id)->get();

        $completed_booking = BookingHandymanMapping::with('bookings')->whereHas('bookings', function($bookings){
            $bookings->where('status','completed');
        })->where('handyman_id',auth()->user()->id)->orderBy('id','DESC')->get();

        $handyman_rating = HandymanRating::where('handyman_id',auth()->user()->id)->orderBy('id','desc')->paginate(10);
        $handyman_rating = HandymanRatingResource::collection($handyman_rating);

        $commission = HandymanType::where('id',$handyman->handymantype_id)->first();

        if (Schema::hasColumn('handyman_payouts', 'status')) {
            $total_revenue = HandymanPayout::where('handyman_id', auth()->user()->id)
                ->where('status', 'paid')
                ->sum('amount') ?? 0;
        } else {
            $total_revenue = CommissionEarning::where('user_type', 'handyman')
                ->where('employee_id', auth()->user()->id)
                ->where('commission_status', 'paid')
                ->sum('commission_amount') ?? 0;
        }
        $remaining_payout  = CommissionEarning::where('employee_id',$handyman->id)->where('commission_status', 'unpaid')->sum('commission_amount') ?? 0;

        $revenuedata = HandymanPayout::selectRaw('sum(amount) as total , DATE_FORMAT(updated_at , "%m") as month' )
                        ->where('handyman_id',auth()->user()->id)
                        ->whereYear('updated_at',date('Y'))
                        ->when(Schema::hasColumn('handyman_payouts','status'), function ($q) {
                            $q->where('status','paid');
                        })
                        // ->whereIn('commission_status', ['unpaid', 'paid'])
                        ->groupBy('month');
        $revenuedata= $revenuedata->get();
        $data['revenueData']    =    [];
        for($i = 1; $i <= 12; $i++ ){
            $revenueData = 0.0;
            foreach($revenuedata as $revenue){
                if($revenue['month'] == $i){

                    $data['revenueData'][] = [
                        $i => $revenue['total']
                    ];
                    $revenueData++;
                }
            }
            if($revenueData == 0){
                $data['revenueData'][] = (object) [] ;
            }
        }

        $notification = count($handyman->unreadNotifications);

        $upcomming_booking = Booking::myBooking()->with('customer')->where('status', 'pending')->orderBy('id', 'DESC')->take(5)->get();
        if(!empty($upcomming_booking)){
            $upcomming_booking = BookingResource::collection($upcomming_booking);
        }

        $response = [
            'status'                        => true,
            'total_cash_in_hand'            => total_cash_in_hand(auth()->user()->id),
            'total_booking'                 => $booking->count(),
            'upcomming_booking'             => $upcomming->count(),
            'today_booking'                 => $today_booking->count(),
            'commission'                    => $commission,
            'handyman_reviews'              => $handyman_rating,
            'total_revenue'                 => $total_revenue,
            'monthly_revenue'               => $data,
            'notification_unread_count'     => $notification,
            'isHandymanAvailable'           => $handyman->is_available,
            'completed_booking'             => $completed_booking->count(),
            'upcomming_booking'             => $upcomming_booking,
            'is_email_verified'             => $handyman->is_email_verified,
            'remaining_payout'              => $remaining_payout,
         ];
         return comman_custom_response($response);

    }
    public function adminDashboard(Request $request){
        $admin = User::find(auth()->user()->id);

        $notification = count($admin->unreadNotifications);

        $services = Booking::with('categoryService')->myBooking()->showServiceCount()->take(5)->get();

        $post_request = PostJobRequest::latest()->take(5)->get();
        $post_requests = PostJobRequestResource::collection($post_request);

        $totalProviders = User::where('user_type', 'provider')->withTrashed()->count();

        $general_setting = Setting::getValueByKey('general-setting','general-setting');
        $total_tax  =    Booking::with('commissionsdata')->whereHas('commissionsdata', function($query){
            $query->whereIn('commission_status', ['unpaid','paid'])->groupBy('booking_id');
        })->sum('final_total_tax') ?? 0;
        $my_earning  =    CommissionEarning::whereIn('user_type',['admin', 'demo_admin'])->whereIn('commission_status', ['unpaid','paid'])->sum('commission_amount') ?? 0;
        $response = [
            'status'                        => true,
            'total_booking'                 => Booking::myBooking()->count(),
            'total_service'                 => Service::myService()->count(),
            'total_provider'                => $totalProviders,
            'total_tax'                     => $total_tax,
            'my_earning'                    => $my_earning,
            'total_revenue'                 => CommissionEarning::whereIn('commission_status', ['paid', 'unpaid'])->sum('commission_amount') ?? 0,
            'monthly_revenue'               => adminEarning(),
            'provider'                      => UserResource::collection(User::myUsers('get_provider')->orderBy('id','DESC')->take(5)->get()),
            'user'                          => UserResource::collection(User::myUsers('get_customer')->orderBy('id','DESC')->take(5)->get()),
            'upcomming_booking'             => BookingResource::collection(Booking::myBooking()->where('status','pending')->orderBy('id','DESC')->take(5)->get()),
            'notification_unread_count'     => $notification,

         ];

         return comman_custom_response($response);
    }
    public function configurations(Request $request){
        $sitesetup = Setting::getValueByKey('site-setup','site-setup');
        $general_setting = Setting::getValueByKey('general-setting','general-setting');
        $service_config = Setting::getValueByKey('service-configurations','service-configurations');
        $social_media = Setting::getValueByKey('social-media','social-media');
        $other_setting = Setting::getValueByKey('OTHER_SETTING','OTHER_SETTING');
        $terms_condition = Setting::getValueByKey('terms_condition','terms_condition');
        $privacy_policy = Setting::getValueByKey('privacy_policy','privacy_policy');
        $help_support = Setting::getValueByKey('help_support','help_support');
        $refund_policy = Setting::getValueByKey('refund_cancellation_policy','refund_cancellation_policy');
        $data_deletion_request = Setting::getValueByKey('data_deletion_request','data_deletion_request');
        $earning_setting = Setting::getValueByKey('earning-setting','earning-setting');
        // Resolve currency dynamically from site setup default_currency (Country)
        try {
            $currencyId = isset($sitesetup->default_currency) ? $sitesetup->default_currency : null;
            $country = $currencyId ? Country::find($currencyId) : null;
            $country_obj = (object) [
                'symbol' => !empty($country) && !empty($country->symbol) ? $country->symbol : '€',
                'currency_code' => strtoupper((string) (!empty($country) && !empty($country->currency_code) ? $country->currency_code : 'EUR')),
            ];
        } catch (\Throwable $e) {
            $country_obj = (object) [
                'symbol' => '€',
                'currency_code' => 'EUR'
            ];
        }
        $user = User::withTrashed()->where('id', (int)$request->input('user_id'))->first();
        $is_user_authorized = false;
        if (!empty($user)) {
            if ($user->status === 0) {
                $is_user_authorized = false;
            } elseif ($user->status === 1) {
                $is_user_authorized = !$user->trashed();
            }
        }
        $response = [
            "site_name"=> $general_setting->site_name,
            "site_description"=> $general_setting->site_description,
            "inquiry_email"=> $general_setting->inquriy_email,
            "helpline_number"=> $general_setting->helpline_number,
            "website"=> $general_setting->website,
            "zipcode"=> $general_setting->zipcode,
            "site_copyright"=> $sitesetup->site_copyright,
            "date_format"=> $sitesetup->date_format,
            "time_format"=> $sitesetup->time_format,
            "time_zone"=> $sitesetup->time_zone,
            "distance_type"=> $sitesetup->distance_type,
            "radius"=> $sitesetup->radious,
            'is_user_authorized' => $is_user_authorized,

            "playstore_url"=> $sitesetup->playstore_url,
            "appstore_url"=> $sitesetup->appstore_url,
            "provider_appstore_url"=> $sitesetup->provider_appstore_url,
            "provider_playstore_url"=> $sitesetup->provider_playstore_url,

            "currency_country_code"=> $country_obj->currency_code,
            "currency_position"=> $sitesetup->currency_position,
            "currency_symbol"=> $country_obj->symbol,
            "currency_code"=> $country_obj->currency_code,
            "decimal_point"=> $sitesetup->digitafter_decimal_point,
            "advance_payment_status"=> $service_config->advance_payment,         
            "cancellation_charge" => isset($service_config->cancellation_charge) ? $service_config->cancellation_charge : 0,
            "cancellation_charge_amount" => isset($service_config->cancellation_charge_amount) ? (double)$service_config->cancellation_charge_amount : 0,
            "cancellation_charge_hours" => isset($service_config->cancellation_charge_hours) ? (double)$service_config->cancellation_charge_hours : 0,
            "slot_service_status"=> $service_config->slot_service,
            "digital_service_status"=> $service_config->digital_services,
            "service_package_status"=> $service_config->service_packages,
            "service_addon_status"=> $service_config->service_addons,
            "job_request_service_status"=> $service_config->post_services,
            "social_login_status"=> $other_setting->social_login,
            "google_login_status"=> $other_setting->google_login,
            "apple_login_status"=> $other_setting->apple_login,
            "otp_login_status"=> $other_setting->otp_login,
            "online_payment_status"=> $other_setting->online_payment,
            "blog_status"=> $other_setting->blog,
            "maintenance_mode"=> $other_setting->maintenance_mode,
            "wallet_status"=> $other_setting->wallet,
            "chat_gpt_status"=> $other_setting->enable_chat_gpt,
            "test_chat_gpt_without_key"=> $other_setting->test_without_key,
            "dashboard_type"=> $other_setting->dashboard_type,
            "force_update_user_app"=> $other_setting->force_update_user_app,
            "user_app_minimum_version"=> (int)$other_setting->user_app_minimum_version,
            "user_app_latest_version"=>  (int)$other_setting->user_app_latest_version,
            "force_update_provider_app"=> $other_setting->force_update_provider_app,
            "provider_app_minimum_version"=> (int)$other_setting->provider_app_minimum_version,
            "provider_app_latest_version"=>  (int)$other_setting->provider_app_latest_version,
            "force_update_admin_app"=> $other_setting->force_update_admin_app,
            "admin_app_minimum_version"=> (int)$other_setting->admin_app_minimum_version,
            "admin_app_latest_version"=>  (int)$other_setting->admin_app_latest_version,
            "firebase_notification_status"=> $other_setting->firebase_notification,

            "facebook_url"=> $social_media->facebook_url,
            "linkedin_url"=> $social_media->linkedin_url,
            "instagram_url"=> $social_media->instagram_url,
            "youtube_url"=> $social_media->youtube_url,
            "twitter_url"=> $social_media->twitter_url,

            "terms_conditions"=> $terms_condition,
            "privacy_policy"=> $privacy_policy,
            "help_support"=> $help_support,
            "refund_policy"=> $refund_policy,
            "data_deletion_request" => $data_deletion_request,
            "earning_type"=> $earning_setting,
            "auto_assign_status" => !empty($other_setting->auto_assign_provider) ? $other_setting->auto_assign_provider: 0

        ];
        if(!empty($request->is_authenticated) && $request->is_authenticated == 1){
            $response["google_map_key"] = $sitesetup->google_map_keys;
            $response["chat_gpt_key"] = $other_setting->chat_gpt_key;
            $response["project_id"] = $other_setting->project_id;
        }

        return comman_custom_response($response);
    }

    function firebaseDetails(Request $request)
    {
        $setting = Setting::getValueByKey('OTHER_SETTING', 'OTHER_SETTING');
        $decodedata = $setting ? json_decode($setting->value) : null;

        $firebase_token = getAccessToken();
        
        // Check if JSON file exists
        $directory = storage_path('app/data');
        $credentialsFiles = \Illuminate\Support\Facades\File::glob($directory . '/*.json');
        $jsonFileExists = !empty($credentialsFiles);
        $jsonFileName = $jsonFileExists ? basename($credentialsFiles[0]) : null;
        
        // Validate JSON file if it exists
        $jsonFileValid = false;
        $jsonFileError = null;
        if ($jsonFileExists) {
            try {
                $jsonContent = file_get_contents($credentialsFiles[0]);
                $jsonData = json_decode($jsonContent, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($jsonData['type']) && $jsonData['type'] === 'service_account') {
                        if (isset($jsonData['private_key']) && isset($jsonData['client_email'])) {
                            $jsonFileValid = true;
                            // Check if project_id matches
                            $jsonProjectId = $jsonData['project_id'] ?? null;
                            $configProjectId = $decodedata->project_id ?? null;
                            if ($jsonProjectId && $configProjectId && $jsonProjectId !== $configProjectId) {
                                $jsonFileError = 'Project ID mismatch: JSON has "' . $jsonProjectId . '" but config has "' . $configProjectId . '"';
                            }
                        } else {
                            $jsonFileError = 'Missing required fields: private_key or client_email';
                        }
                    } else {
                        $jsonFileError = 'Not a valid service account file (type should be "service_account")';
                    }
                } else {
                    $jsonFileError = 'Invalid JSON: ' . json_last_error_msg();
                }
            } catch (\Exception $e) {
                $jsonFileError = 'Error reading file: ' . $e->getMessage();
            }
        }

        $data = [
            'project_id' => $decodedata->project_id ?? null,
            'firebase_notification_enabled' => $decodedata->firebase_notification ?? 0,
            'firebase_token' => $firebase_token,
            'token_status' => $firebase_token ? 'Generated successfully' : 'Generation failed',
            'json_file_exists' => $jsonFileExists,
            'json_file_name' => $jsonFileName,
            'json_file_valid' => $jsonFileValid,
            'json_file_error' => $jsonFileError,
            'json_file_path' => $jsonFileExists ? $credentialsFiles[0] : null,
            'api_version' => 'FCM V1 (HTTP v1) - Correct',
            'legacy_api_status' => 'Disabled (Expected - Legacy was deprecated)',
            'diagnosis' => $this->diagnoseFirebaseIssue($firebase_token, $jsonFileExists, $jsonFileValid, $decodedata),
        ];

        $message = trans('messages.firebase_data');

        return response()->json(['status' => true, 'data' => $data, 'message' => $message]);
    }
    
    private function diagnoseFirebaseIssue($token, $jsonExists, $jsonValid, $settings)
    {
        if ($token) {
            return 'Everything is working correctly!';
        }
        
        if (!$jsonExists) {
            return 'Issue: JSON file not found. Upload service account JSON file to storage/app/data/';
        }
        
        if (!$jsonValid) {
            return 'Issue: JSON file is invalid. Re-download service account JSON from Firebase Console.';
        }
        
        if (!($settings->firebase_notification ?? 0)) {
            return 'Issue: Firebase notifications disabled in settings. Enable it in admin panel.';
        }
        
        if (!($settings->project_id ?? null)) {
            return 'Issue: Project ID not configured. Set it in admin panel settings.';
        }
        
        return 'Issue: Token generation failed. Check service account permissions in Google Cloud Console.';
    }
}
