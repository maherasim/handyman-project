<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\HelpDeskController;
use App\Http\Controllers\API\PostJobRequestController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\User\UserController;
use App\Models\Blog;
use App\Models\Booking;
use App\Models\BookingHandymanMapping;
use App\Models\BookingRating;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\FrontendSetting;
use App\Models\HandymanRating;
use App\Models\HelpDesk;
use App\Models\PostJobRequest;
use App\Models\ProviderServiceAddressMapping;
use App\Models\ProviderSubscription;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\User;
use App\Models\UserFavouriteService;
use Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FrontendController extends Controller
{

    public function index(Request $request)
    {
        $service_id = $request->id;
        $auth_user_id = null;
        $favourite = null;

        if (auth()->check() && auth()->user()->hasRole('user')) {
            $auth_user_id = auth()->user()->id;
            $favourite = UserFavouriteService::where('user_id', $auth_user_id)->get();
        }

        // Fetch all section data from FrontendSetting
        $sectionData = [];
        $sectionKeys = ['section_1', 'section_2', 'section_3', 'section_4', 'section_5', 'section_6', 'section_7', 'section_9', 'section_10'];

        foreach ($sectionKeys as $key) {
            $section = FrontendSetting::where('key', $key)->first();
            $sectionData[$key] = $section ? json_decode($section->value, true) : null;
        }

        // Job List
        $postJobRequestIds = $sectionData['section_10']['post_job_requests_id'] ?? [];
        $jobRequests = PostJobRequest::whereIn('id', $postJobRequestIds)->get();

        // Category List
        $categories = $sectionData['section_2']['category_id'] ?? [];
        $categoryrequest = Category::whereIn('id', $categories)->get();

        // Service List
        // Service List with average ratings and review counts
        $servicerequest = $sectionData['section_3']['service_id'] ?? [];
        // dd($servicerequest);
        // $completedBookingCount = Booking::whereIn('service_id', $servicerequest)
        // ->where('status', 'completed') // Only count completed bookings
        // ->count();
        $servicerequest = Service::whereIn('id', $servicerequest)
            ->with(['serviceRating']) // Assuming a serviceRating relationship exists
            ->get()
            ->map(function ($service) {
                $ratings = BookingRating::where('service_id', $service->id)->pluck('rating')->toArray();
                $totalReviews = count($ratings);
                $avgRating = $totalReviews > 0 ? array_sum($ratings) / $totalReviews : 0;

                $service->total_reviews = $totalReviews;
                $service->avg_rating = round($avgRating, 1);

                $service->booking_count = Booking::where('service_id', $service->id)
                    ->where('status', 'completed') // Only count completed bookings
                    ->count();

                return $service;
            });

        $featuredrequest = $sectionData['section_4']['service_id'] ?? [];
        // dd( $featuredrequest);
        $completedBookingCount = Booking::whereIn('service_id', $featuredrequest)
            ->where('status', 'completed') // Only count completed bookings
            ->count();
        // dd( $completedBookingCount);
        $featuredrequest = Service::whereIn('id', $featuredrequest)
            ->with(['serviceRating']) // Assuming a serviceRating relationship exists
            ->get()
            ->map(function ($service) {
                $ratings = BookingRating::where('service_id', $service->id)->pluck('rating')->toArray();
                $totalReviews = count($ratings);
                $avgRating = $totalReviews > 0 ? array_sum($ratings) / $totalReviews : 0;

                $service->total_reviews = $totalReviews;
                $service->avg_rating = round($avgRating, 1);

                $service->booking_count = Booking::where('service_id', $service->id)
                    ->where('status', 'completed') // Only count completed bookings
                    ->count();

                return $service;
            });
        // Featured Service List

        // Service Configuration
        $settings = Setting::where('type', 'service-configurations')->where('key', 'service-configurations')->first();
        $serviceconfig = $settings ? json_decode($settings->value) : null;
        $postjobservice = $serviceconfig ? $serviceconfig->post_services : null;

        // Calculate average rating
        $ratings = BookingRating::pluck('rating')->toArray();
        $averageRating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
        $totalRating = number_format($averageRating, 2);
        $favouriteServiceData = [];
        $apiRequest = new Request(['service_id' => $service_id, 'per_page' => 'all']);
        $serviceController = app(ServiceController::class);
        if (!empty(auth()->user())) {
            $favouriteServiceResponse = $serviceController->getUserFavouriteService($apiRequest);
            $favouriteService = json_decode($favouriteServiceResponse->content(), true);
            $favouriteServiceData = $favouriteService['data'] ?? [];
            $userId = auth()->user()->id;
            $favouriteService = collect($favouriteServiceData)->filter(function ($item) use ($userId, $service_id) {
                return isset($item['user_id']) && $item['user_id'] == $userId
                    && isset($item['service_id']) && $item['service_id'] == $service_id;
            })->toArray();
        } else {
            $favouriteService = collect();
            $userId = 0;
        }

        return view('landing-page.index', compact('sectionData', 'favouriteService', 'completedBookingCount', 'postjobservice', 'auth_user_id', 'favourite', 'totalRating', 'jobRequests', 'categoryrequest', 'servicerequest', 'featuredrequest'));
    }

    public function userLoginView(Request $request)
    {
        $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
        $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
        return view('landing-page.login', compact('sectionData'));
    }

    public function partnerRegistrationView(Request $request)
    {
        $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
        $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
        return view('landing-page.providerRegister', compact('sectionData'));
    }

    public function userRegistrationView(Request $request)
    {
        $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
        $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
        return view('landing-page.register', compact('sectionData'));
    }

    public function forgotPassword(Request $request)
    {
        $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
        $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
        return view('landing-page.forgot-password', compact('sectionData'));
    }

    public function catgeoryList(Request $request)
    {
        $headerSection = FrontendSetting::where('key', 'heder-menu-setting')->first();
        $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;

        if ($sectionData['categories'] == 0) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }

        return view('landing-page.category');
    }

    public function subCatgeoryList(Request $request)
    {
        $category_id = $request->category_id;
        return view('landing-page.sub-category', compact('category_id'));
    }

    public function serviceList(Request $request)
    {
        // Simple Blade-based listing (no Vue) is the default now.
        // Pass mode=vue to see the old Vue page.
        if ($request->get('mode') !== 'vue') {
            $filters = [
                'category_id'   => $request->get('category_id'),
                'subcategory_id'=> $request->get('subcategory_id'),
                'provider_id'   => $request->get('provider_id'),
                'country_id'    => $request->get('country_id'),
                'city_id'       => $request->get('city_id'),
                'price_min'     => $request->get('price_min'),
                'price_max'     => $request->get('price_max'),
                'sort'          => $request->get('sort'), // price_asc, price_desc, newest
                'q'             => $request->get('q'),
            ];




            $categories    = Category::where('status', 1)->orderBy('name')->get(['id','name'])->take(10);
            if(!empty($filters['category_id'])){
                $selected = Category::find($filters['category_id']);
                if($selected && !$categories->contains('id', $selected->id)) $categories->push($selected);
            }

            $subcategories = SubCategory::where('status', 1)->orderBy('name')->get(['id','name','category_id'])->take(10);
            if(!empty($filters['subcategory_id'])){
                $selected = SubCategory::find($filters['subcategory_id']);
                if($selected && !$subcategories->contains('id', $selected->id)) $subcategories->push($selected);
            }

            $providers     = User::where('user_type','provider')->where('status',1)->orderBy('display_name')->get(['id','display_name'])->take(10);
            if(!empty($filters['provider_id'])){
                $selected = User::find($filters['provider_id']);
                if($selected && !$providers->contains('id', $selected->id)) $providers->push($selected);
            }

            $countries     = Country::orderBy('name')->get(['id','name'])->take(10);
            if(!empty($filters['country_id'])){
                $selected = Country::find($filters['country_id']);
                if($selected && !$countries->contains('id', $selected->id)) $countries->push($selected);
            }

            // cities table doesn't have country_id; join states to fetch country_id for dependent dropdown
            $cities        = City::query()
                ->leftJoin('states', 'states.id', '=', 'cities.state_id')
                ->orderBy('cities.name')
                ->get(['cities.id', 'cities.name', 'cities.state_id', 'states.country_id'])->take(10);
            if(!empty($filters['city_id'])){
                $selected = City::find($filters['city_id']);
                if($selected && !$cities->contains('id', $selected->id)) $cities->push($selected);
            }

            $query = Service::where('service_type','service')->where('status',1);
            if (!empty($filters['q'])) {
                $query->where('name', 'like', "%{$filters['q']}%");
            }
            if (!empty($filters['category_id'])) {
                $query->where('category_id', (int)$filters['category_id']);
            }
            if (!empty($filters['subcategory_id'])) {
                $query->where('subcategory_id', (int)$filters['subcategory_id']);
            }
            if (!empty($filters['provider_id'])) {
                $query->where('provider_id', (int)$filters['provider_id']);
            }
            if (!empty($filters['country_id'])) {
                $query->where('country_id', (int)$filters['country_id']);
            }
            if (!empty($filters['city_id'])) {
                $query->where('city_id', (int)$filters['city_id']);
            }
            if ($filters['price_min'] !== null && $filters['price_min'] !== '') {
                $query->where('price', '>=', (float)$filters['price_min']);
            }
            if ($filters['price_max'] !== null && $filters['price_max'] !== '') {
                $query->where('price', '<=', (float)$filters['price_max']);
            }

            if ($filters['sort'] === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($filters['sort'] === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $services = $query->with(['providers','city','country'])->paginate(12)->withQueryString();

            return view('landing-page.service-simple', compact(
                'services', 'categories', 'subcategories', 'providers', 'countries', 'cities', 'filters'
            ));
        }

        $headerSection = FrontendSetting::where('key', 'heder-menu-setting')->first();
        $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;

        if ($sectionData['service'] == 0) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }

        if ($request->provider_id) {
            $id = $request->provider_id;
            $type = "provider-service";
        } else if ($request->subcategory_id) {
            $id = $request->subcategory_id;
            $type = 'subcategory-service';
        } else if ($request->category_id) {
            $id = $request->category_id;
            $type = 'category-service';
        } else {
            $id = null;
            $type = null;
        }

        if ($request->latitude && $request->longitude) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
        } else {
            $latitude = null;
            $longitude = null;
        }
        return view('landing-page.service', compact('id', 'type', 'latitude', 'longitude'));
    }

    public function providerList(Request $request)
    {
        $headerSection = FrontendSetting::where('key', 'heder-menu-setting')->first();
        $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;

        if ($sectionData['provider'] == 0) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }

        return view('landing-page.provider');
    }

    public function blogList(Request $request)
    {
        $settings = Setting::whereIn('type', ['service-configurations', 'OTHER_SETTING'])
            ->whereIn('key', ['service-configurations', 'OTHER_SETTING'])
            ->get()
            ->keyBy('type');
        $othersetting = $settings->has('OTHER_SETTING') ? json_decode($settings['OTHER_SETTING']->value) : null;

        if (optional($othersetting)->blog == 0) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }

        return view('landing-page.blog');
    }

    public function bookingList(Request $request)
    {
        $headerSection = FrontendSetting::where('key', 'heder-menu-setting')->first();
        $sectionData = $headerSection ? json_decode($headerSection->value, true) : null;

        if ($sectionData['bookings'] == 0) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }

        return view('landing-page.booking');
    }

    public function postJobList(Request $request)
    {
        return view('landing-page.post-job');
    }

    public function relatedService(Request $request)
    {
        $service_id = $request->id;
        $serviceController = app(ServiceController::class);
        $apiRequest = new Request(['service_id' => $service_id, 'per_page' => 'all']);
        $service = $serviceController->getServiceDetail($apiRequest);
        $serviceData = json_decode($service->content(), true);
        return view('landing-page.related-service', compact('service'));
    }

    public function categoryDetail(Request $request)
    {
        $category_id = $request->id;
        $category = Category::where('id', $category_id)->first();
        $sub_category = SubCategory::where('category_id', $category_id)->where('status', 1)->get();
        $serviceController = app(ServiceController::class);
        $apiRequest = new Request(['category_id' => $category_id, 'per_page' => 'all']);
        $serviceResponse = $serviceController->getServiceList($apiRequest);
        $service = $serviceResponse->getData()->data ?? [];

        if ($request->provider_id) {
            $id = $request->provider_id;
            $type = "provider-service";
        } else if ($request->subcategory_id) {
            $id = $request->subcategory_id;
            $type = 'subcategory-service';
        } else if ($request->category_id) {
            $id = $request->category_id;
            $type = 'category-service';
        } else {
            $id = null;
            $type = null;
        }

        if ($request->latitude && $request->longitude) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
        } else {
            $latitude = null;
            $longitude = null;
        }

        return view('landing-page.category-detail', compact('category', 'sub_category', 'service', 'id', 'type', 'latitude', 'longitude'));
    }

    public function categoryDetailServiceDatatable(Datatables $datatable, Request $request)
    {
        $query = Service::where('service_type', 'service')->where('status', 1);

        // Apply filters (existing logic)
        if ($request->provider_id) {
            $query->where('provider_id', $request->provider_id);
        }
        if ($request->type == 'subcategory-service') {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('subcategory_id', $request->id);
            });
        }
        if ($request->id) {
            $query->where('category_id', $request->id);
        }

        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $get_distance = getSettingKeyValue('site-setup', 'radious') ?? 50;
            $get_unit = getSettingKeyValue('site-setup', 'distance_type') ?? 'km';
            $locations = $query->locationService($request->latitude, $request->longitude, $get_distance, $get_unit);
            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id', $locations)->get()->pluck('service_id');
            $query->with('providerServiceAddress')->whereIn('id', $service_in_location);
        }

        if (default_earning_type() === 'subscription') {
            $query->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe', 1);
            });
        }

        // Handle filters
        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where('name', 'LIKE', '%' . $filter['search'] . '%');
        }
        if (isset($filter['selectedCategory'])) {
            $query->where('category_id', $filter['selectedCategory']);
        }
        if (isset($filter['selectedProvider'])) {
            $query->where('provider_id', $filter['selectedProvider']);
        }
        if (isset($filter['selectedPriceRange'])) {
            $priceRange = explode('-', $filter['selectedPriceRange']);
            if (count($priceRange) === 2) {
                $minPrice = $priceRange[0];
                $maxPrice = $priceRange[1];
                $query->whereBetween('price', [$minPrice, $maxPrice]);
            }
        }

        // Handle sorting
        if (isset($filter['selectedSortOption'])) {
            if ($filter['selectedSortOption'] == "best_selling") {
                $bestSellingServiceIds = Booking::select('service_id', \DB::raw('COUNT(service_id) as service_count'))
                    ->where('status', 'completed')
                    ->groupBy('service_id')
                    ->orderByDesc('service_count')
                    ->pluck('service_id')
                    ->toArray();

                if (!empty($bestSellingServiceIds)) {
                    $query->whereIn('id', $bestSellingServiceIds)
                        ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $bestSellingServiceIds) . ")"));
                }
            }
            if ($filter['selectedSortOption'] == "top_rated") {
                $topRatedServiceIds = BookingRating::select(
                    'service_id',
                    \DB::raw('COALESCE(AVG(rating), 0) as avg_rating'),
                    \DB::raw('COUNT(rating) as total_reviews')
                )
                    ->groupBy('service_id')
                    ->orderByDesc('avg_rating')
                    ->orderByDesc('total_reviews')
                    ->pluck('service_id')
                    ->toArray();

                if (!empty($topRatedServiceIds)) {
                    $allServiceIds = $query->pluck('id')->toArray();
                    $otherServiceIds = array_diff($allServiceIds, $topRatedServiceIds);
                    $orderedServiceIds = array_merge($topRatedServiceIds, $otherServiceIds);
                    $query->whereIn('id', $orderedServiceIds)
                        ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $orderedServiceIds) . ")"));
                }
            }
            if ($filter['selectedSortOption'] == "newest") {
                $query->orderBy('created_at', 'desc');
            }
        }

        if ($request->limit) {

            $query->limit($request->limit);
        }

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $totalReviews = $data->id ? BookingRating::where('service_id', $data->id)->count() : 0;
                $totalRating = $data->serviceRating ? (float)number_format(max($data->serviceRating->avg('rating'), 0), 2) : 0;
                if (!empty(auth()->user())) {
                    $favouriteService = $data->getUserFavouriteService()->where('user_id', auth()->user()->id)->get();
                } else {
                    $favouriteService = collect();
                }

                $completedBookingCount = Booking::where('service_id', $data->id)
                    ->where('status', 'completed')
                    ->count();

                $col = 3;

                // Compute provider plan icon (free/silver/gold)
                $plan_icon = asset('images/freepng.png');
                $provider = $data->providers;
                if ($provider && $provider->providerSubscription) {
                    $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                    if (str_contains($rawPlan, 'silver')) {
                        $plan_icon = asset('images/icon/silverpng.png');
                    } elseif (str_contains($rawPlan, 'gold')) {
                        $plan_icon = asset('images/goldpng.png');
                    }
                }

                return view('service.datatable-card', compact('data', 'totalReviews', 'totalRating', 'favouriteService', 'completedBookingCount', 'col', 'plan_icon'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }


    public function blogDetail(Request $request)
    {
        $blog_id = $request->id;
        $blog_data = Blog::all();
        $blogController = app(BlogController::class);
        $apiRequest = new Request(['blog_id' => $blog_id]);
        $blogResponse = $blogController->getBlogDetail($apiRequest);
        $blog = $blogResponse->getData()->blog_detail ?? [];
        $blog->read_time = calculateReadingTime($blog->description);

        return view('landing-page.blog-detail', compact('blog', 'blog_data'));
    }


    public function providerDetail(Request $request)
    {
        $provider_id = $request->id;
        $userController = app(UserController::class);
        $apiRequest = new Request(['id' => $provider_id]);
        $providerData = $userController->userDetail($apiRequest);
        $providerData = json_decode($providerData->content(), true);
        $why_choose_me = json_decode($providerData['data']['why_choose_me'], true);

        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $datetime = json_decode($sitesetup->value);

        $completed_services = Booking::where('provider_id', $providerData['data']['id'])
            ->where('status', 'completed')
            ->count();

        $servicerating = Service::where('provider_id', $providerData['data']['id'])
            ->with('serviceRating')
            ->get();

        $allRatings = $servicerating->flatMap(function ($service) {
            return $service->serviceRating->filter(function ($rating) {
                return in_array($rating->rating, [4, 5]);
            });
        });

        $satisfy_customers = $allRatings->pluck('customer_id')->unique()->count();

        if (!empty(auth()->user()) && auth()->user()->hasRole('user')) {
            $auth_user_id = auth()->user()->id;
            $favourite = UserFavouriteService::where('user_id', $auth_user_id)->get();
        } else {
            $auth_user_id = null;
            $favourite = null;
        }
        // dd($provider_id);
        // Fetch provider subscription
        $subscription = ProviderSubscription::where('user_id', (int)$provider_id)->first();

        // dd(  $subscription);
        // Determine image path based on subscription (path per requirements)
        $imagePath = 'images/freepng.png'; // Default free icon at /public/images/freepng.png
        if ($subscription) {
            $rawPlan = strtolower(trim($subscription->plan_type ?? $subscription->title ?? ''));
            if (str_contains($rawPlan, 'silver')) {
                $imagePath = 'images/icon/silverpng.png';
            } elseif (str_contains($rawPlan, 'gold')) {
                $imagePath = 'images/goldpng.png';
            } else {
                $imagePath = 'images/freepng.png';
            }
        }
        // dd( $imagePath);
        return view('landing-page.ProviderDetails', compact(
            'providerData',
            'why_choose_me',
            'datetime',
            'completed_services',
            'satisfy_customers',
            'auth_user_id',
            'favourite',
            'imagePath'
        ));
    }


    public function handymanDetail(Request $request)
    {
        $handyman_id = $request->id;
        $userController = app(UserController::class);
        $apiRequest = new Request(['id' => $handyman_id]);
        $handymanData = $userController->userDetail($apiRequest);
        $handymanData = json_decode($handymanData->content(), true);
        $why_choose_me = json_decode($handymanData['data']['why_choose_me'], true);

        $handyman_rating = HandymanRating::where('handyman_id', $handymanData['data']['id'])->orderBy('created_at', 'desc')->get();
        $total_handyman_rating = $handyman_rating->count();

        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $datetime = json_decode($sitesetup->value);

        $completed_services = BookingHandymanMapping::whereHas('bookings', function ($query) {
            $query->where('status', 'completed');
        })->where('handyman_id', $handymanData['data']['id'])->count();

        $satisfy_customers = $handyman_rating->filter(function ($rating) {
            return in_array($rating->rating, [4, 5]);
        })->pluck('customer_id')->unique()->count();

        return view('landing-page.HandymanDetails', compact('handymanData', 'why_choose_me', 'handyman_rating', 'total_handyman_rating', 'datetime', 'completed_services', 'satisfy_customers'));
    }

    public function serviceDetail(Request $request)
    {
        $service_id = $request->id;
        $serviceController = app(ServiceController::class);
        $apiRequest = new Request(['service_id' => $service_id, 'per_page' => 'all']);
        $service = $serviceController->getServiceDetail($apiRequest);
        $serviceData = json_decode($service->content(), true);

        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $date_time = json_decode($sitesetup->value);
        $favouriteServiceData = [];

        if (!empty(auth()->user())) {
            $favouriteServiceResponse = $serviceController->getUserFavouriteService($apiRequest);
            $favouriteService = json_decode($favouriteServiceResponse->content(), true);
            $favouriteServiceData = $favouriteService['data'] ?? [];
            $userId = auth()->user()->id;
            $favouriteService = collect($favouriteServiceData)->filter(function ($item) use ($userId, $service_id) {
                return isset($item['user_id']) && $item['user_id'] == $userId
                    && isset($item['service_id']) && $item['service_id'] == $service_id;
            })->toArray();
        } else {
            $favouriteService = collect();
            $userId = 0;
        }

        if (isset($serviceData['provider']) && isset($serviceData['provider']['id'])) {
            $completed_services = Booking::where('provider_id', $serviceData['provider']['id'])
                ->where('status', 'completed')
                ->count();

            $knownLanguageArray = json_decode($serviceData['provider']['known_languages'] ?? '[]', true);
        } else {
            $completed_services = 0;
            $knownLanguageArray = [];
        }

        // Compute provider plan icon for service detail
        $providerPlanIcon = 'images/freepng.png';
        if (isset($serviceData['provider']['id'])) {
            $sub = ProviderSubscription::where('user_id', (int)$serviceData['provider']['id'])->first();
            if ($sub) {
                $rawPlan = strtolower(trim($sub->plan_type ?? $sub->title ?? ''));
                if (str_contains($rawPlan, 'silver')) {
                    $providerPlanIcon = 'images/icon/silverpng.png';
                } elseif (str_contains($rawPlan, 'gold')) {
                    $providerPlanIcon = 'images/goldpng.png';
                }
            }
        }

        $price = $serviceData['service_detail']['price'] ?? 0;
        $discount = $serviceData['service_detail']['discount'] ?? 0;
        $subtotal = $discount != 0 ? ($price - ($price * $discount / 100)) : $price;


        // $total_ratings = BookingRating::where('service_id', $serviceData['service_detail']['id'])->get();
        $serviceIdForRatings = (int)($serviceData['service_detail']['id'] ?? 0);
        $total_ratings = $serviceIdForRatings > 0 ? BookingRating::where('service_id', $serviceIdForRatings)->get() : collect();
        return view('landing-page.ServiceDetail', compact('serviceData', 'favouriteService', 'date_time', 'completed_services', 'knownLanguageArray', 'subtotal', 'total_ratings', 'favouriteServiceData', 'userId', 'providerPlanIcon'));
    }


    public function privacyPolicy(Request $request)
    {
        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();
        return view('landing-page.PrivacyPolicy', compact('privacy_policy'));
    }

    public function termConditions(Request $request)
    {
        $term_condition = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();
        return view('landing-page.TermConditions', compact('term_condition'));
    }

    public function refundPolicy(Request $request)
    {
        $refund_policy = Setting::where('type', 'refund_cancellation_policy')->where('key', 'refund_cancellation_policy')->first();
        return view('landing-page.RefundPolicy', compact('refund_policy'));
    }

    public function helpSupport(Request $request)
    {
        $help_support = Setting::where('type', 'help_support')->where('key', 'help_support')->first();
        return view('landing-page.helpSupport', compact('help_support'));
    }

    public function DataDeletion(Request $request)
    {
        $data_deletion_request = Setting::where('type', 'data_deletion_request')->where('key', 'data_deletion_request')->first();
        return view('landing-page.dataDeletion', compact('data_deletion_request'));
    }

    public function Imprint(Request $request)
    {
        $imprint = Setting::where('type', 'imprint')->where('key', 'imprint')->first();

        return view('landing-page.imprint', compact('imprint'));
    }

    public function AGB(Request $request)
    {
        $imprint = Setting::where('type', 'agb')->where('key', 'agb')->first();

        return view('landing-page.agb', compact('imprint'));
    }

    public function bookServiceView(Request $request)
    {

        $service_id = $request->id;
        $service = Service::where('id', $service_id)
            ->with(['providers.city','providers.country','category','serviceRating','serviceAddon','city','country'])
            ->first();

        $service->service_image = getSingleMedia($service, 'service_attachment', null);
        $service->category_name = optional($service->category)->name;
        $service->subcategory_name = optional($service->subcategory)->name;
        $service->provider_name = optional($service->providers)->display_name;
        $service->provider_city = optional(optional($service->providers)->city)->name;
        $service->provider_country = optional(optional($service->providers)->country)->name;
        $service->service_city = optional($service->city)->name;
        $service->service_country = optional($service->country)->name;
        $service->provider_image = getSingleMedia($service->providers, 'profile_image', null);
        $total_reviews = optional($service->serviceRating)->count();
        $total_rating = optional($service->serviceRating)->sum('rating');
        $service->total_reviews = $total_reviews;
        $service->total_rating = $total_reviews > 0 ? number_format($total_rating / $total_reviews, 2) : 0;
        // Total bookings of this service
        $service->total_service_bookings = Booking::where('service_id', $service_id)->count();
        $serviceconfig = Setting::getValueByKey('service-configurations', 'service-configurations');
        $global_advance_payment = isset($serviceconfig->global_advance_payment) ? $serviceconfig->global_advance_payment : 0;
        $globalAdvancePaymentPercentage = $global_advance_payment == 1 ? $serviceconfig->advance_paynment_percantage : 0;
        $service->advance_payment_amount = $service->is_enable_advance_payment == 1 ? ($service->advance_payment_amount === null ? 0 : (double)$service->advance_payment_amount) : (double)$globalAdvancePaymentPercentage;
        $service->is_enable_advance_payment = $service->is_enable_advance_payment == 1 ? $service->is_enable_advance_payment : $global_advance_payment;


        $coupons = Coupon::where('expire_date', '>', date('Y-m-d H:i'))
            ->where('status', 1)
            ->whereHas('serviceAdded', function ($coupons) use ($service_id) {
                $coupons->where('service_id', $service_id);
            })->get();

        $user_id = Auth::id();

        $matchedTax = Tax::where('status', 1)
            ->where('id', $service->tax_country_id)
            ->first();


        $taxes = [];

        if ($matchedTax) {
            $taxes[] = [
                'id' => $matchedTax->id,
                'title' => $matchedTax->title,
                'type' => $matchedTax->type,
                'value' => $matchedTax->value,
            ];
        }
        $availableserviceslot = null;

        if ($service->is_slot == 1) {

            $availableserviceslot = getServiceTimeSlot($service->provider_id);

        }

        //  $taxes = $transformedData;

        if ($request->package_id) {
            $service = ServicePackage::where('id', $request->package_id)->first();
            $service->package_image = $service->getFirstMedia('package_attachment')->getUrl();
            $service->service_id = $service_id;
            $service->total_price = $service->getTotalPrice();
        }

        $serviceaddon = null;
        if ($request->addons) {
            $addon_id = explode(',', $request->addons);
            $serviceaddons = ServiceAddon::whereIn('id', $addon_id)->get();
            $serviceaddon = $serviceaddons->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addon->price,
                    'serviceaddon_image' => getSingleMedia($addon, 'serviceaddon_image', null),
                ];
            });
        }
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = json_decode($sitesetup->value);
        $googlemapkey = $sitesetupdata->google_map_keys;
        $user = auth()->user();
        $wallet = $user ? $user->wallet : null;
        $wallet_amount = $wallet ? $wallet->amount : 0;

        $payment_type = $request->payment_type == 'full_payment' ? 'paid' : $request->payment_type;
        $booking_id = $request->booking_id ?? null;
        $booking = Booking::with('bookingExtraCharge')->find($booking_id);
        $total_booking_amount = $booking->total_amount ?? 0;
        $total_advance_paid_amount = $booking->advance_paid_amount ?? 0;

        return view('landing-page.BookService', compact('service', 'coupons', 'taxes', 'user_id', 'availableserviceslot', 'serviceaddon', 'googlemapkey', 'wallet_amount', 'payment_type', 'booking_id', 'total_booking_amount', 'total_advance_paid_amount'));
    }

     public function showdetails($id)
    {
        // Eager-load related data including customer with location and bids
        $jobrequest = PostJobRequest::with(['city', 'country', 'state', 'category', 'subCategory', 'provider', 'customer.city', 'customer.country', 'postBidList'])->find($id);
        if (!$jobrequest) {
            abort(404);
        }

        // Increment view count
        try {
            $jobrequest->increment('total_views');
        } catch (\Throwable $e) {
            // Silent fail - view tracking shouldn't break the page
        }

        // Build attachments array similar to service detail
        $attachments = [];
        if (!empty($jobrequest->image)) {
            $attachments[] = asset('storage/' . ltrim($jobrequest->image, '/'));
        }
        $extraImages = [];
        if (!empty($jobrequest->images)) {
            $extraImages = is_array($jobrequest->images) ? $jobrequest->images : json_decode($jobrequest->images, true) ?? [];
        }
        foreach ($extraImages as $imgPath) {
            $attachments[] = asset('storage/' . ltrim($imgPath, '/'));
        }

        // Aggregates
        $totalBids = $jobrequest->total_bids;
        $jobsPublishedCount = PostJobRequest::where('customer_id', $jobrequest->customer_id)->count();

        return view('job.job_details', compact('jobrequest', 'totalBids', 'attachments', 'jobsPublishedCount'));
    }

    public function bookPostJobView(Request $request)
    {
        $user_id = Auth::id();
        $post_job_id = $request->id;
        $postJobController = app(PostJobRequestController::class);
        $apiRequest = new Request(['post_request_id' => $post_job_id]);
        $postJob = $postJobController->getPostRequestDetail($apiRequest);
        return view('landing-page.BookPostJob', compact('postJob', 'user_id'));
    }


    public function postJob()
    {
        $user_id = Auth::id();
        return view('landing-page.post-job-show', compact('user_id'));
    }

    public function bookingDetail(Request $request, $id)
    {
        if (!auth()->check()) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }
        $booking_id = $id;
        $findBooking = Booking::where('id', $booking_id)->first();
        if (is_null($findBooking)) {
            return redirect()->route('booking.list')->withErrors(trans('messages.booking_not_found'));;
        }
        $bookingController = app(BookingController::class);
        $apiRequest = new Request(['booking_id' => $booking_id, 'customer_id' => auth()->user()->id]);
        $booking = $bookingController->getBookingDetail($apiRequest);

        $user = auth()->user();
        $wallet = $user ? $user->wallet : null;
        $wallet_amount = $wallet ? $wallet->amount : 0;
        $bookingData = json_decode($booking->content(), true);

        $serviceconfig = Setting::getValueByKey('service-configurations', 'service-configurations');
        $advancePaymentPercentage = isset($serviceconfig->advance_paynment_percantage) ? $serviceconfig->advance_paynment_percantage : 0;
        $global_advance_payment = isset($serviceconfig->global_advance_payment) ? $serviceconfig->global_advance_payment : 0;
        $bookingData['service']['is_enable_advance_payment'] = $bookingData['service']['type'] == 'fixed' ? ($bookingData['service']['is_enable_advance_payment'] == 1 ? $bookingData['service']['is_enable_advance_payment'] : $global_advance_payment) : 0;
        $advancepaymentamount = ($bookingData['booking_detail']['total_amount'] * $bookingData['service']['advance_payment_amount']) / 100;
        // $bookingData['cancellationcharges'] = isset($serviceconfig->cancellation_charge_amount) ?  (double)$serviceconfig->cancellation_charge_amount: 0;
        // $bookingData['cancellation_charge_amount'] = $findBooking->getCancellationCharges();

        if ($bookingData['booking_detail']['customer_id'] != auth()->user()->id) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $date_time = $sitesetup ? json_decode($sitesetup->value, true) : null;
        return view('landing-page.BookingDetail', compact('booking', 'wallet_amount', 'date_time', 'bookingData', 'advancepaymentamount'));
    }

    public function postJobDetail(Request $request)
    {
        $post_job_id = $request->id;
        $postJobController = app(PostJobRequestController::class);
        $apiRequest = new Request(['post_request_id' => $post_job_id]);
        $postJob = $postJobController->getPostRequestDetail($apiRequest);
        return view('landing-page.post-job-detail', compact('postJob'));
    }

    public function favouriteServiceList(Request $request)
    {
        $user = auth()->user();
        $favouriteService = UserFavouriteService::where('user_id', $user->id)->get();

        $isEmpty = $favouriteService->isEmpty();

        return view('landing-page.FavouriteService', compact('isEmpty'));
    }

    public function servicePackageList(Request $request)
    {
        $service_id = $request->id;
        $serviceController = app(ServiceController::class);
        $apiRequest = new Request(['service_id' => $service_id]);
        $service = $serviceController->getServiceDetail($apiRequest);
        $serviceData = json_decode($service->content(), true);
        return view('landing-page.ServicePackages', compact('service', 'serviceData'));
    }

    public function ratingList(Request $request)
    {

        if ($request->handyman_id) {
            $query = HandymanRating::query()->orderBy('created_at', 'desc');

        } else {
            $query = BookingRating::query()->orderBy('created_at', 'desc');
        }

        $review_count = 0;

        if ($request->provider_id) {
            $id = $request->provider_id;
            $type = "provider-rating";

            $query = $query->whereHas('service', function ($q) use ($id) {
                $q->where('provider_id', $id);
            });

            $review_count = count($query->get());
        } else if ($request->handyman_id) {
            $id = $request->handyman_id;
            $type = 'handyman-rating';

            $query = $query->where('handyman_id', $id);

            $review_count = count($query->get());
        } else if ($request->service_id) {
            $id = $request->service_id;
            $type = "service-rating";
            $query = $query->where('service_id', $id);
            $review_count = count($query->get());
        }

        return view('landing-page.RatingAll', compact('id', 'type', 'review_count'));
    }

    public function categoryDatatable(Datatables $datatable, Request $request)
    {
        $query = Category::query();

        $query = $query->where('status', 1);

        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where('name', 'LIKE', '%' . $filter['search'] . '%');
        }

        // Apply alphabetical order by name
        $query = $query->orderByRaw('LOWER(name) asc');

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                return view('category.datatable-card', compact('data'));
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }


    public function subCategoryDatatable(Datatables $datatable, Request $request)
    {
        $query = SubCategory::query();
        $query = $query->where('status', 1);
        $query = $query->where('category_id', $request->category_id);
        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                return view('subcategory.datatable-card', compact('data'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function jobDatatable(Request $request)
    {
        // Initialize the query builder
        $query = PostJobRequest::with(['country', 'city']);


        // Apply filters based on request input
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Optionally apply sorting
        if ($request->has('sort') && $request->sort == 1) {
            $query->orderBy('price', 'asc');
        } elseif ($request->has('sort') && $request->sort == 2) {
            $query->orderBy('price', 'desc');
        }

        // Fetch the filtered results
        $jobrequest = $query->get();

        // Fetch categories, subcategories, customers, countries, and cities for dropdowns
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $customers = User::where('user_type', 'user')->get();
        $countries = Country::get();
        $cities = City::take(10)->get();
        $favouriteService = auth()->check()
            ? PostJobRequest::where('provider_id', auth()->id())->get()
            : collect(); // Empty collection if not logged in


        return view('job.datatable-card', compact('jobrequest', 'categories', 'subcategories', 'customers', 'countries', 'cities', 'favouriteService'));
    }


    public function serviceDatatable(Datatables $datatable, Request $request)
    {
        $query = Service::where('service_type', 'service')->where('status', 1)->with('media');

        // Apply filters (existing logic)
        if ($request->type == 'provider-service') {
            $query->where('provider_id', $request->id);
        }
        if ($request->type == 'subcategory-service') {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('subcategory_id', $request->id);
            });
        }
        if ($request->type == 'category-service') {
            $query->where('category_id', $request->id);
        }

        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $get_distance = getSettingKeyValue('site-setup', 'radious') ?? 50;
            $get_unit = getSettingKeyValue('site-setup', 'distance_type') ?? 'km';
            $locations = $query->locationService($request->latitude, $request->longitude, $get_distance, $get_unit);
            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id', $locations)->get()->pluck('service_id');
            $query->with('providerServiceAddress')->whereIn('id', $service_in_location);
        }

        if (default_earning_type() === 'subscription') {
            $query->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe', 1);
            });
        }

        // Handle filters
        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where('name', 'LIKE', '%' . $filter['search'] . '%');
        }
        if (isset($filter['selectedCategory'])) {
            $query->where('category_id', $filter['selectedCategory']);
        }
        if (isset($filter['selectedSubCategory'])) {
            $query->where('subcategory_id', $filter['selectedSubCategory']);
        }
        if (isset($filter['selectedProvider'])) {
            $query->where('provider_id', $filter['selectedProvider']);
        }

        if (isset($filter['selectedCountry'])) {
            $query->where('country_id', $filter['selectedCountry']);
        }
        if (isset($filter['selectedCity'])) {
            $query->where('city_id', $filter['selectedCity']);
        }
        if (isset($filter['selectedPriceRange'])) {
            $priceRange = explode('-', $filter['selectedPriceRange']);
            if (count($priceRange) === 2) {
                $minPrice = $priceRange[0];
                $maxPrice = $priceRange[1];
                $query->whereBetween('price', [$minPrice, $maxPrice]);
            }
        }

        // Handle sorting
        if (isset($filter['selectedSortOption'])) {
            if ($filter['selectedSortOption'] == "best_selling") {
                $bestSellingServiceIds = Booking::select('service_id', \DB::raw('COUNT(service_id) as service_count'))
                    ->where('status', 'completed')  // Only count completed bookings
                    ->groupBy('service_id')
                    ->orderByDesc('service_count')
                    ->pluck('service_id')
                    ->toArray();

                if (!empty($bestSellingServiceIds)) {
                    $query->whereIn('id', $bestSellingServiceIds)
                        ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $bestSellingServiceIds) . ")"));
                }
            }
            if ($filter['selectedSortOption'] == "top_rated") {
                $topRatedServiceIds = BookingRating::select(
                    'service_id',
                    \DB::raw('COALESCE(AVG(rating), 0) as avg_rating'),
                    \DB::raw('COUNT(rating) as total_reviews')
                )
                    ->groupBy('service_id')
                    ->orderByDesc('avg_rating')
                    ->orderByDesc('total_reviews')
                    ->pluck('service_id')
                    ->toArray();

                if (!empty($topRatedServiceIds)) {
                    $allServiceIds = $query->pluck('id')->toArray();
                    $otherServiceIds = array_diff($allServiceIds, $topRatedServiceIds);
                    $orderedServiceIds = array_merge($topRatedServiceIds, $otherServiceIds);
                    $query->whereIn('id', $orderedServiceIds)
                        ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $orderedServiceIds) . ")"));
                }
            }
            if ($filter['selectedSortOption'] == "newest") {
                $query->orderBy('created_at', 'desc');
            }
        }

        // Add a column to count completed bookings
        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $totalReviews = $data->id ? BookingRating::where('service_id', $data->id)->count() : 0;
                $totalRating = $data->serviceRating ? (float)number_format(max($data->serviceRating->avg('rating'), 0), 2) : 0;
                if (!empty(auth()->user())) {
                    $favouriteService = $data->getUserFavouriteService()->where('user_id', auth()->user()->id)->get();
                } else {
                    $favouriteService = collect();
                }

                // Count the number of completed bookings for this service
                $completedBookingCount = Booking::where('service_id', $data->id)
                    ->where('status', 'completed')  // Only count completed bookings
                    ->count();

                // Compute provider plan icon (free/silver/gold)
                $plan_icon = asset('images/freepng.png');
                $provider = $data->providers;
                if ($provider && $provider->providerSubscription) {
                    $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                    if (str_contains($rawPlan, 'silver')) {
                        $plan_icon = asset('images/icon/silverpng.png');
                    } elseif (str_contains($rawPlan, 'gold')) {
                        $plan_icon = asset('images/goldpng.png');
                    }
                }

                // Preload service image URL - reload service fresh to ensure media is accessible
                // DataTables may serialize the model, so we need a fresh instance
                $service = Service::find($data->id);
                $serviceImage = $service ? getSingleMedia($service, 'service_attachment', null) : asset('images/default.png');

                return view('service.datatable-card', compact('data', 'totalReviews', 'totalRating', 'favouriteService', 'completedBookingCount', 'plan_icon', 'serviceImage'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function blogDatatable(Datatables $datatable, Request $request)
    {
        $query = Blog::query();
        $query = $query->where('status', 1);
        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where('title', 'LIKE', '%' . $filter['search'] . '%');
        }

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                return view('blog.datatable-card', compact('data'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function providerDatatable(Datatables $datatable, Request $request)
    {
        $query = User::query()
            ->where('user_type', 'provider')
            ->where('status', 1)
            ->with('providerSubscription'); // Make sure relation is eager loaded

        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('first_name', 'LIKE', '%' . $filter['search'] . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $filter['search'] . '%');
            });
        }

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                // Calculate provider rating from booking_ratings table
                // Match bookings.provider_id with provider id, then match bookings.id with booking_ratings.booking_id
                $providerRating = 0;
                $totalReviews = 0;
                
                // Get all booking IDs for this provider
                $bookingIds = Booking::where('provider_id', $data->id)->pluck('id');
                
                if ($bookingIds->isNotEmpty()) {
                    // Get all ratings for these bookings
                    $ratings = BookingRating::whereIn('booking_id', $bookingIds)
                        ->whereNotNull('rating')
                        ->get();
                    
                    if ($ratings->isNotEmpty()) {
                        $providerRating = (float)number_format($ratings->avg('rating'), 2);
                        $totalReviews = $ratings->count();
                    }
                }

                // Default to free plan icon (path per requirements)
                $plan_icon = asset('images/freepng.png');

                if ($data->providerSubscription) {
                    $rawPlan = strtolower(trim($data->providerSubscription->plan_type ?? $data->providerSubscription->title ?? ''));
                    if (str_contains($rawPlan, 'silver')) {
                        $plan_icon = asset('images/icon/silverpng.png');
                    } elseif (str_contains($rawPlan, 'gold')) {
                        $plan_icon = asset('images/goldpng.png');
                    } else {
                        $plan_icon = asset('images/freepng.png');
                    }
                }

                // Pass rating and review count to the blade view
                return view('provider.datatable-card', compact('data', 'providerRating', 'totalReviews', 'plan_icon'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])->toJson();
    }


    public function bookingDatatable(Datatables $datatable, Request $request)
    {
        $query = Booking::query();
        $query = $query->where('customer_id', auth()->user()->id);
        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->WhereHas('service', function ($q) use ($filter) {
                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
            });
        }
        if (isset($filter['booking_date_range'])) {
            $dateRange = explode(' to ', $filter['booking_date_range']);
            
            if (count($dateRange) === 2) {
                $startDate = trim($dateRange[0]);
                $endDate = trim($dateRange[1]);
                
                // Validate date format (Y-m-d)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                    // Show booking if ANY slot falls within the date range
                    $query->whereHas('slots', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date', [$startDate, $endDate]);
                    });
                }
            }
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        $query->orderByDesc('id');

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $service = optional($data->service);
                $serviceimage = getSingleMedia($service, 'service_attachment', null);
                $total_rating = (float)number_format(max(optional($data->service)->serviceRating->avg('rating'), 0), 2);
                $advancepaid = 0;

                if ($data->status == 'cancelled') {
                    $advancepaid = $data->advance_paid_amount == null ? 0 : (double)$data->advance_paid_amount;
                }
                $refund_amount = $advancepaid > 0 ? (double)$advancepaid - $data->cancellation_charge_amount : 0;
                $refund_status = $advancepaid > 0 ? 'completed' : null;
                $payment = $data->payment()->orderBy('id', 'desc')->first();
                return view('booking.datatable-card', compact('data', 'payment', 'total_rating', 'serviceimage', 'refund_amount', 'refund_status'));
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function postJobDatatable(Datatables $datatable, Request $request)
    {
        $query = PostJobRequest::myPostJob()->whereIn('status', ['requested', 'accepted', 'assigned']);
        $filter = $request->filter;
        if (isset($filter['search'])) {
            $searchTerm = $filter['search'];
            $query->where(function ($query) use ($searchTerm) {
                $query->where('status', 'LIKE', '%' . $searchTerm . '%');
                $query->orWhereHas('postServiceMapping', function ($serviceQuery) use ($searchTerm) {
                    $serviceQuery->where('service_id', '!=', null)
                        ->whereExists(function ($subServiceQuery) use ($searchTerm) {
                            $subServiceQuery->selectRaw(1)
                                ->from('services')
                                ->whereRaw('services.id = post_job_service_mappings.service_id')
                                ->where('name', 'LIKE', '%' . $searchTerm . '%');
                        });
                });
            });
        }
        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $services = optional($data->postServiceMapping);
                $serviceImages = $services->map(function ($service) {
                    $serviceId = $service->service_id;
                    $image = optional(Service::find($serviceId)->getFirstMedia('service_attachment'))->getUrl();
                    return $image;
                });
                return view('postrequest.datatable-card', compact('data', 'serviceImages'));
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }


    public function favouriteServiceDatatable(Datatables $datatable, Request $request)
    {
        $user = auth()->user();

        $favouriteServiceIds = UserFavouriteService::where('user_id', $user->id)->pluck('service_id')->toArray();

        $query = Service::whereIn('id', $favouriteServiceIds);

        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->where('name', 'LIKE', '%' . $filter['search'] . '%');
        }
        if (isset($filter['selectedCategory'])) {
            $query->where('category_id', $filter['selectedCategory']);
        }
        if (isset($filter['selectedProvider'])) {
            $query->where('provider_id', $filter['selectedProvider']);
        }
        if (isset($filter['selectedPriceRange'])) {
            $priceRange = explode('-', $filter['selectedPriceRange']);

            if (count($priceRange) === 2) {
                $minPrice = $priceRange[0];
                $maxPrice = $priceRange[1];
                $query->whereBetween('price', [$minPrice, $maxPrice]);
            }
        }
        if (isset($filter['selectedSortOption'])) {
            if ($filter['selectedSortOption'] == "best_selling") {
                $bestSellingServiceIds = Booking::select('service_id', \DB::raw('COUNT(service_id) as service_count'))
                    ->groupBy('service_id')
                    ->orderByDesc('service_count')
                    ->pluck('service_id')
                    ->toArray();

                $query->whereIn('id', $bestSellingServiceIds)
                    ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $bestSellingServiceIds) . ")"));
            }
            if ($filter['selectedSortOption'] == "top_rated") {
                $topRatedServiceIds = BookingRating::select('service_id', \DB::raw('COALESCE(AVG(rating), 0) as avg_rating'))
                    ->groupBy('service_id')
                    ->orderByDesc('avg_rating')
                    ->pluck('service_id')
                    ->toArray();

                $query->whereIn('id', $topRatedServiceIds)
                    ->orderByRaw(\DB::raw("FIELD(id, " . implode(',', $topRatedServiceIds) . ")"));
            }
            if ($filter['selectedSortOption'] == "newest") {
                $query->orderBy('created_at', 'desc');
            }
        }


        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $totalReviews = BookingRating::where('service_id', $data->id)->count();
                $totalRating = count($data->serviceRating) > 0
                    ? (float)number_format(max($data->serviceRating->avg('rating'), 0), 2)
                    : 0;

                if (!empty(auth()->user())) {
                    $favouriteService = $data->getUserFavouriteService()->where('user_id', auth()->user()->id)->get();
                } else {
                    $favouriteService = collect();
                }
                // Compute provider plan icon (free/silver/gold)
                $plan_icon = asset('images/freepng.png');
                $provider = $data->providers;
                if ($provider && $provider->providerSubscription) {
                    $rawPlan = strtolower(trim($provider->providerSubscription->plan_type ?? $provider->providerSubscription->title ?? ''));
                    if (str_contains($rawPlan, 'silver')) {
                        $plan_icon = asset('images/icon/silverpng.png');
                    } elseif (str_contains($rawPlan, 'gold')) {
                        $plan_icon = asset('images/goldpng.png');
                    }
                }
                return view('service.datatable-card', compact('data', 'totalReviews', 'totalRating', 'favouriteService', 'plan_icon'));
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function ratingDatatable(Datatables $datatable, Request $request)
    {

        $id = $request->id;
        if ($request->type == 'handyman-rating') {
            $query = HandymanRating::query()->orderBy('created_at', 'desc');
            $query = $query->where('handyman_id', $id);
        } else {
            $query = BookingRating::query()->orderBy('created_at', 'desc');
        }
        if ($request->type == 'provider-rating') {
            $query = $query->whereHas('service', function ($q) use ($id) {
                $q->where('provider_id', $id);
            });
        } elseif ($request->type == 'service-rating') {
            $query = $query->where('service_id', $id);
        }

        $filter = $request->filter;
        if (isset($filter['search'])) {
            $query->WhereHas('customer', function ($q) use ($filter) {
                $q->where('display_name', 'LIKE', '%' . $filter['search'] . '%');
            });
        }

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                return view('ratingreview.datatable-card', compact('data'));
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function userSubscribe(Request $request)
    {
        $emailData['email'] = $request->email;
        $emailData['title'] = env('APP_NAME');
        $emailData['body'] = 'Thank you for subscribe us.';
        try {
            \Mail::send('customer.subscribe_email', $emailData, function ($message) use ($emailData) {
                $message->to($emailData['email'])
                    ->subject($emailData['title']);
            });

            $messagedata = __('landingpage.subscribe_msg');
            return comman_message_response($messagedata);
        } catch (\Throwable $th) {
            $messagedata = __('messages.something_wrong');
            return comman_message_response($messagedata);
        }
    }

    public function helpdeskList(Request $request)
    {
        $query = HelpDesk::query();
        $data = $query->where('employee_id', auth()->user()->id)->get();
        $addpermission = auth()->user()->can('helpdesk add') ? true : false;
        return view('landing-page.helpdesk', compact('data', 'addpermission'));
    }

    public function helpdeskDatatable(Datatables $datatable, Request $request)
    {
        $query = HelpDesk::query();
        $query = $query->where('employee_id', auth()->user()->id);
        $filter = $request->filter;


        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }


        $query->orderByDesc('id');

        $datatable = $datatable->eloquent($query)
            ->editColumn('name', function ($data) {
                $service = optional($data->service);
                $serviceimage = getSingleMedia($service, 'service_attachment', null);
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
                return view('helpdesk.datatable-card', compact('data', 'datetime'));
            });

        return $datatable->rawColumns(['name'])
            ->toJson();
    }

    public function helpdeskDetail(Request $request)
    {

        $helpdesk_id = $request->id;
        $findHelpdesk = HelpDesk::with('helpdeskactivity')->where('id', $helpdesk_id)->first();
        if (empty($findHelpdesk)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.helpdesk')]);
            return redirect(route('helpdesk.list'))->withError($msg);
        }
        if ($findHelpdesk['employee_id'] != auth()->user()->id) {
            return redirect(route('frontend.index'))->withErrors(trans('messages.demo_permission_denied'));
        }
        $helpdeskController = app(HelpDeskController::class);
        $apiRequest = new Request(['id' => $helpdesk_id]);
        $helpdesk = $helpdeskController->getHelpDeskDetail($apiRequest);

        $helpdeskData = json_decode($helpdesk->content(), true);
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $date_time = $sitesetup ? json_decode($sitesetup->value, true) : null;
        return view('landing-page.HelpdeskDetail', compact('findHelpdesk', 'date_time', 'helpdeskData'));
    }

    public function ajaxCityList(Request $request)
    {
        $term = $request->q;
        $country_id = $request->country_id;

        $query = City::query()->select('cities.id', 'cities.name', 'cities.state_id');

        if ($country_id) {
            $query->leftJoin('states', 'states.id', '=', 'cities.state_id')
                  ->where('states.country_id', $country_id);
        }

        if ($term) {
            $query->where('cities.name', 'like', "%$term%");
        }

        if($country_id){
            $cities = $query->get();
        } else {
            $cities = $query->limit(50)->get();
        }

        return response()->json($cities->map(function($c){
            return ['id' => $c->id, 'text' => $c->name];
        }));
    }
    public function subCatgeoryListfor(Request $request)
    {
        $category_id = $request->category_id;
        $subcategories = SubCategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }
}
