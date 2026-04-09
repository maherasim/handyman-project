<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Http\Requests\UserRequest;
use Hash;
use App\Http\Resources\API\UserResource;
use App\Http\Resources\API\ServiceResource;
use Illuminate\Support\Facades\Password;
use App\Models\Booking;
use App\Models\BookingRating;
use App\Models\PostJobBidCustomerRating;
use App\Models\Wallet;
use App\Models\HandymanRating;
use App\Http\Resources\API\HandymanRatingResource;
use App\Http\Resources\API\BookingRatingResource;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Models\ProviderDocument;
use App\Http\Resources\API\DocumentResource;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class UserController extends Controller
{
    use NotificationTrait;

    public function migrateFreshSeed()
    {

        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--no-interaction' => true,
        ]);


        Artisan::call('db:seed', [
            '--force' => true,
            '--no-interaction' => true,
        ]);


     return response()->json([ 'data' => 'Database migrated and seeded successfully' ], 200 );

    }


public function register(UserRequest $request)
{
    $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
    $admin = json_decode($sitesetup->value);
    date_default_timezone_set($admin->time_zone ?? 'UTC');

    $input = $request->all();
    $email = $input['email'];
    $username = $input['username'];
    $password = $input['password'];

    $input['display_name'] = $input['first_name'] . " " . $input['last_name'];
    $input['user_type'] = $input['user_type'] ?? 'user';
    $input['password'] = Hash::make($password);
    $input['contact_number'] = $input['contact_number'] ?? null;

    // Limit check for handyman under subscription plan
    if ($request->provider_id !== null && $request->id == null && default_earning_type() === 'subscription') {
        if (!empty($input['provider_id'] && $input['user_type'] === 'handyman')) {
            $exceed = get_provider_plan_limit($input['provider_id'], 'handyman');
            if (!empty($exceed)) {
                $message = $exceed == 1
                    ? __('messages.limit_exceed', ['name' => __('messages.handyman')])
                    : __('messages.not_in_plan', ['name' => __('messages.handyman')]);

                return $request->is('api/*')
                    ? comman_message_response($message)
                    : redirect()->back()->withErrors($message);
            }
        }
    }

    if (in_array($input['user_type'], ['handyman', 'provider', 'user'])) {
        $input['status'] = $input['status'] ?? 0;
    }

    // Check for existing user
    $user = User::withTrashed()
        ->where(function ($query) use ($email, $username) {
            $query->where('email', $email)->orWhere('username', $username);
        })
        ->first();

    if ($user) {
        if ($user->deleted_at === null) {
            return comman_custom_response(['message' => trans('messages.login_form')]);
        }

        return comman_custom_response([
            'message' => trans('messages.deactivate'),
            'Isdeactivate' => 1,
        ]);
    }

    // Create new user
    $user = User::create($input);
    $user->assignRole($input['user_type']);

    // Send verification email to all users (providers and users)
    $verificationLink = route('verify', ['id' => $user->id]);
    Mail::to($user->email)->send(new VerificationEmail($verificationLink));

    // Create wallet for all user types (provider, handyman, and user)
    if (in_array($user->user_type, ['provider', 'handyman', 'user'])) {
        Wallet::create([
            'title' => $user->display_name,
            'user_id' => $user->id,
            'amount' => 0
        ]);
    }

    // Optional Vue app check
    if (!empty($input['loginfrom']) && $input['loginfrom'] === 'vue-app') {
        if ($user->user_type !== 'user') {
            return comman_custom_response([
                'message' => trans('messages.save_form', ['form' => $input['user_type']]),
                'data' => $user
            ]);
        }
    }

    // Send activity notification
    $this->sendNotification([
        'activity_type' => 'register',
        'user_id' => $user->id,
        'user_type' => $user->user_type,
        'user_email' => $user->email,
        'user_name' => $user->display_name,
    ]);

    // Return response without login token
    return comman_custom_response([
        'message' => 'Email verification link has been sent to your email. Please verify before logging in.',
        'data' => $user
    ]);
}



    public function login(Request $request)
    {
        // dd($request->all());
        $Isactivate = request('Isactivate');
        if($Isactivate == 1){
            $user = User::withTrashed()
            ->where('email', request('email'))
            ->first();
            if($user){
                $user->restore();
            }else{
                $message = trans('auth.failed');
                return comman_message_response($message, 406);
            }

        }

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){

            $user = Auth::user();
            if($user->status == 0){
                Auth::logout();
            }
            if(request('loginfrom') === 'vue-app'){
                if($user->user_type != 'user'){
                    $message = trans('auth.not_able_login');
                    return comman_message_response($message,400);
                }
            }
            $user->save();

            $success = $user;
            $success['user_role'] = $user->getRoleNames();
            $success['api_token'] = $user->createToken('auth_token')->plainTextToken;
            $success['profile_image'] = getSingleMedia($user,'profile_image',null);
            $is_verify_provider = false;

            if($user->user_type == 'provider')
            {
                $is_verify_provider = verify_provider_document($user->id);
                $success['subscription'] = get_user_active_plan($user->id);

                if(is_any_plan_active($user->id) == 0 && $success['is_subscribe'] == 0 ){
                    $success['subscription'] = user_last_plan($user->id);
                }
                $success['is_subscribe'] = is_subscribed_user($user->id);
                $success['provider_id'] = admin_id();

            }
            if($user->user_type == 'provider' || $user->user_type == 'user'){
                $wallet = Wallet::where('user_id',$user->id)->first();
                if( $wallet == null){
                    $wallet = array(
                        'title' => $user->display_name,
                        'user_id' => $user->id,
                        'amount' => 0
                    );
                    Wallet::create($wallet);
                }
            }
            $success['is_verify_provider'] = (int) $is_verify_provider;
            unset($success['media']);
            unset($user['roles']);

            if($success->user_type == 'handyman' && $success->provider_id == null){
                $message = trans('auth.assign_provider_msg');
                return comman_message_response($message,406);
            }

                return response()->json([ 'data' => $success ], 200 );
        }
        else{
            $message = trans('auth.failed');
            return comman_message_response($message,406);
        }
    }

    public function userList(Request $request)
    {
        $user_type = isset($request['user_type']) ? $request['user_type'] : 'handyman';
        $type = isset($request['type']) ? $request['type'] : '';
        $status = isset($request['status']) ? $request['status'] : 1;
        $all = isset($request['is_user_list_all']) ? $request['is_user_list_all'] : null;

        $user_list = User::orderBy('is_available', 'desc')
        ->orderBy('id', 'desc')
        ->where('user_type', $user_type);

        if(!empty($status)){
            $user_list = $user_list->where('status',$status);
        }

        // Removed subscription-based filtering so providers are not restricted by is_subscribe
        if(auth()->user() !== null && auth()->user()->hasRole(['admin', 'provider'])){
            $user_list = $user_list->withTrashed();
            if($request->has('keyword') && isset($request->keyword))
            {
                $user_list = $user_list->where('display_name','like','%'.$request->keyword.'%');
            }
            if($user_type == 'handyman' && $status == 0){
                $user_list = $user_list->orWhere('provider_id',NULL)->where('user_type' ,'handyman');
            }
            if($user_type == 'handyman' && $status == 1){
                $user_list = $user_list->whereNotNull('provider_id')->where('user_type' ,'handyman');
            }

        }
        if($request->has('provider_id'))
        {
            $user_list = $user_list->where('provider_id',$request->provider_id)->withTrashed();
        }
        if($request->has('city_id') && !empty($request->city_id))
        {
            $user_list = $user_list->where('city_id',$request->city_id);
        }
        if(!empty($all) && $all == "all" ){
            $user_list = User::orderBy('is_available', 'desc')
            ->orderBy('id', 'desc')
            ->whereIn('user_type', ['provider','handyman','user'])->where('status', 1);
        }
        if($request->has('keyword') && isset($request->keyword))
        {
            $user_list = $user_list->where('display_name','like','%'.$request->keyword.'%');
        }
        if($request->has('booking_id')){
            $booking_data = Booking::find($request->booking_id);

            $service_address = $booking_data->handymanByAddress;
            if($service_address != null)
            {
                $user_list = $user_list->where('service_address_id', $service_address->id);
            }
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $user_list->count();
            }
        }

        $user_list = $user_list->paginate($per_page);

        $items = UserResource::collection($user_list);

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

    public function userDetail(Request $request)
    {
        $id = $request->id;

        $user = User::with(['country', 'state', 'city', 'providertype', 'handymantype'])->find($id);
        $message = __('messages.detail');
        if(empty($user)){
            $message = __('messages.user_not_found');
            return comman_message_response($message,400);
        }

        $service = [];
        $handyman_rating = [];
        $handyman = [];
        $profile_array = [];

        if($user->user_type == 'provider')
        {
            $service = Service::where('provider_id',$id)->where('status',1)->orderBy('id','desc')->paginate(10);
            $service = ServiceResource::collection($service);
            $handyman_rating = HandymanRating::where('handyman_id', $id)->publicVisible()->orderBy('id', 'desc')->paginate(10);
            $handyman_rating = HandymanRatingResource::collection($handyman_rating);
            $handyman_staff = User::where('user_type','handyman')->where('provider_id',$id)->where('is_available',1)->get();
            $handyman = UserResource::collection($handyman_staff);

            if(!empty($handyman_staff)){
                foreach ($handyman_staff as $image) {
                    $profile_array[] = $image->login_type !== null ? $image->social_image : getSingleMedia($image, 'profile_image',null);
                }
            }
        }
        $user_detail = new UserResource($user);
        $document = ProviderDocument::where('provider_id',$id)->get();
        
        // Calculate completed jobs count for provider
        $completed_jobs = 0;
        if($user->user_type == 'provider'){
            $completed_jobs = Booking::where('provider_id', $id)
                ->where('status', 'completed')
                ->count();
        }
        
        if($user->user_type == 'handyman'){
            $handyman_rating = HandymanRating::where('handyman_id', $id)->publicVisible()->orderBy('id', 'desc')->paginate(10);
            $handyman_rating = HandymanRatingResource::collection($handyman_rating);
        }

        $response = [
            'data' => $user_detail,
            'service' => $service,
            'handyman_rating_review' => $handyman_rating,
            'handyman_staff' => $handyman,
            'handyman_image' => $profile_array,
            'document_detail' => $document,
            'completed_jobs' => $completed_jobs,
        ];
        return comman_custom_response($response);

    }

    public function changePassword(Request $request){
        $user = User::where('id',\Auth::user()->id)->first();

        if($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message,406);
        }

        $hashedPassword = $user->password;

        $match = Hash::check($request->old_password, $hashedPassword);

        $same_exits = Hash::check($request->new_password, $hashedPassword);
        if ($match)
        {
            if($same_exits){
                $message = __('messages.old_new_pass_same');
                return comman_message_response($message,406);
            }

			$user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            $message = __('messages.password_change');
            return comman_message_response($message,200);
        }
        else
        {
            $message = __('messages.valid_password');
            return comman_message_response($message);
        }
    }

    public function updateProfile(Request $request)
    {
       // dd($request->all());
        \Illuminate\Support\Facades\Log::info('API updateProfile request payload', [
            'input' => $request->all()
        ]);
        $user = \Auth::user();
        if($request->has('id') && !empty($request->id)){
            $user = User::where('id',$request->id)->first();
        }
        if($user == null){
            return comman_message_response(__('messages.no_record_found'),400);
        }

        $data=$request->all();

        // Ensure availability is a string, not an array
        if (isset($data['availability']) && is_array($data['availability'])) {
            $data['availability'] = !empty($data['availability']) ? (string)($data['availability'][0] ?? null) : null;
        }
        
        // Convert availability from old format (1/0 or 'Full-time'/'Part-time') to new format (full_time/part_time)
        if (isset($data['availability'])) {
            $availability = $data['availability'];
            if ($availability == '1' || $availability == 1 || $availability == 'Full-time') {
                $data['availability'] = 'full_time';
            } elseif ($availability == '0' || $availability == 0 || $availability == 'Part-time') {
                $data['availability'] = 'part_time';
            }
            // If it's already 'full_time' or 'part_time', keep it as is
        }
        
        // Ensure language_option is a string, not an array
        if (isset($data['language_option']) && is_array($data['language_option'])) {
            $data['language_option'] = !empty($data['language_option']) ? (string)($data['language_option'][0] ?? 'en') : 'en';
        }

        $why_choose_me=[

            'why_choose_me_title'=>$request->why_choose_me_title,
            'why_choose_me_reason' => isset($request->why_choose_me_reason) && is_string($request->why_choose_me_reason)
            ? array_filter(json_decode($request->why_choose_me_reason), function ($value) {
                return $value !== null;
            })
            : null,

        ];

        $data['why_choose_me']=($why_choose_me);

        $user->fill($data)->update();

        if ($request->hasFile('profile_image')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        } elseif ($request->filled('profile_image_base64')) {
            $base64 = $request->input('profile_image_base64');
            $normalized = $base64;
            $extension = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $extension = strtolower($type[1]);
            } else {
                $normalized = 'data:image/jpeg;base64,' . $base64;
            }
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $extension;
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromBase64($normalized)->usingFileName($filename)->toMediaCollection('profile_image');
        } elseif ($request->filled('profile_image_url')) {
            $url = $request->input('profile_image_url');
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    $user->clearMediaCollection('profile_image');
                    $user->addMediaFromUrl($url)->toMediaCollection('profile_image');
                } catch (\Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl $e) {
                    // Log the error but don't fail the profile update
                    \Log::warning('Failed to download profile image from URL: ' . $url, [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                } catch (\Exception $e) {
                    // Log any other media library errors
                    \Log::warning('Failed to process profile image URL: ' . $url, [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $user_data = User::with(['country', 'state', 'city', 'providertype', 'handymantype'])->find($user->id);

        $message = __('messages.updated');

        if($user->login_type !== null && $user->login_type !== 'mobile'){

            $user_data['profile_image'] =$user->social_image ? $user->social_image : getSingleMedia($user_data, 'profile_image', null);

        }else{

            $user_data['profile_image'] =$user->profile_image ? $user->profile_image : getSingleMedia($user_data, 'profile_image', null);
        }

        $user_data['user_role'] = $user->getRoleNames();

        unset($user_data['roles']);
        unset($user_data['media']);

        $response = [
            'data' => $user_data,
            'message' => $message
        ];
        return comman_custom_response( $response );
    }

    public function logout(Request $request){
        $auth = Auth::user();

        if($request->is('api*')){

           if(!Auth::guard('sanctum')->check()) {
            return response()->json(['status' => false, 'message' => __('messages.user_not_logged_in')]);
           }

          $user = Auth::guard('sanctum')->user();

          $user->tokens()->delete();

        return comman_message_response('Logout successfully');

       }
         Auth::logout();

        return comman_message_response('Logout successfully');

    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($response), 'status' => true], 200)
            : response()->json(['message' => __($response), 'status' => false], 406);
    }

    public function socialLogin(Request $request)
    {
        $input = $request->all();

        if($input['login_type'] === 'mobile'){
            $user_data = User::where('username',$input['username'])->where('login_type','mobile')->first();
        }else{
            $user_data = User::where('email',$input['email'])->first();

        }

        if( $user_data != null ) {
            if( !isset($user_data->login_type) || $user_data->login_type  == '' ){
                if($request->login_type === 'google'){
                    $message = __('validation.unique',['attribute' => 'email' ]);
                } else {
                    $message = __('validation.unique',['attribute' => 'username' ]);
                }
                return comman_message_response($message,400);
            }

            $user_data->update($input);

            $message = __('messages.login_success');
        } else {

            if($request->login_type === 'google')
            {
                $key = 'email';
                $value = $request->email;
            } else {
                $key = 'username';
                $value = $request->username;
            }

            $trashed_user_data = User::where($key,$value)->whereNotNull('login_type')->withTrashed()->first();

            if ($trashed_user_data != null && $trashed_user_data->trashed())
            {
                if($request->login_type === 'google'){
                    $message = __('validation.unique',['attribute' => 'email' ]);
                } else {
                    $message = __('validation.unique',['attribute' => 'username' ]);
                }
                return comman_message_response($message,400);
            }

            if($request->login_type === 'mobile' && $user_data == null ){
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false
                ];
                return comman_custom_response($otp_response);
            }
            if($request->login_type === 'mobile' && $user_data != null){
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => true
                ];
                return comman_custom_response($otp_response);
            }

            $password = !empty($input['accessToken']) ? $input['accessToken'] : $input['email'];

            $input['user_type']  = "user";
            $input['display_name'] = $input['first_name']." ".$input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
            $user = User::create($input);

            $user->assignRole($input['user_type']);

            $user_data = User::where('id',$user->id)->first();
            $message = trans('messages.save_form',['form' => $input['user_type'] ]);
        }

        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;
        if($user_data->login_type !== null && $user_data->login_type !== 'mobile'){

            $user_data['profile_image'] = $user_data->social_image ? $user_data->social_image : getSingleMedia($user_data, 'profile_image', null);

        }else{

            $user_data['profile_image'] = $user_data->profile_image ? $user_data->profile_image : getSingleMedia($user_data, 'profile_image', null);
        }
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $user_data
        ];
        return comman_custom_response($response);
    }

    public function userStatusUpdate(Request $request)
    {
        $user_id =  $request->id;
        $user = User::where('id',$user_id)->first();

        if($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message,400);
        }
        $user->status = $request->status;
        $user->save();

        $message = __('messages.update_form',['form' => __('messages.status') ]);
        $response = [
            'data' => new UserResource($user),
            'message' => $message
        ];
        return comman_custom_response($response);
    }
    public function contactUs(Request $request){
        try {
            \Mail::send('contactus.contact_email',
            array(
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'subject' => $request->get('subject'),
                'phone_no' => $request->get('phone_no'),
                'user_message' => $request->get('user_message'),
            ), function($message) use ($request)
            {
                $message->from($request->email);
                $message->to(env('MAIL_FROM_ADDRESS'));
            });
            $messagedata = __('messages.contact_us_greetings');
            return comman_message_response($messagedata);
        } catch (\Throwable $th) {
            $messagedata = __('messages.something_wrong');
            return comman_message_response($messagedata);
        }

    }
    public function handymanAvailable(Request $request){
        $user_id =  $request->id;
        $user = User::where('id',$user_id)->first();

        if($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message,400);
        }
        $user->is_available = $request->is_available;
        $user->save();

        $message = __('messages.update_form',['form' => __('messages.status') ]);
        $response = [
            'data' => new UserResource($user),
            'message' => $message
        ];
        return comman_custom_response($response);
    }
    public function handymanReviewsList(Request $request){
        $id = $request->handyman_id;
        $handyman_rating_data = HandymanRating::where('handyman_id', $id)->publicVisible();

        $per_page = config('constant.PER_PAGE_LIMIT');

        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $handyman_rating_data->count();
            }
        }

        $handyman_rating_data = $handyman_rating_data->orderBy('created_at','desc')->paginate($per_page);

        $items = HandymanRatingResource::collection($handyman_rating_data);
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

    public function providerReviewsList(Request $request){
        $providerId = (int) $request->provider_id;

        if (!$providerId) {
            return comman_message_response(__('messages.provider_id_required'), 400);
        }

        // Provider reviews from booking_ratings (bookings) and post_job_bid_customer_ratings (post job) — same as listing count
        $bookingIds = Booking::where('provider_id', $providerId)->pluck('id');
        $bookingRatings = $bookingIds->isEmpty()
            ? collect()
            : BookingRating::whereIn('booking_id', $bookingIds)
                ->publicVisible()
                ->with(['customer', 'booking', 'service'])
                ->orderBy('created_at', 'desc')
                ->get();
        $postJobBidRatings = PostJobBidCustomerRating::where('provider_id', $providerId)
            ->publicVisible()
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $mapBookingRating = function ($r) {
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
        $mapPostJobRating = function ($r) {
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
        $allReviews = $bookingRatings->map($mapBookingRating)
            ->concat($postJobBidRatings->map($mapPostJobRating))
            ->sortByDesc('_sort_at')
            ->values();

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

        $response = [
            'pagination' => [
                'total_items' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'totalPages' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'next_page' => $paginator->nextPageUrl(),
                'previous_page' => $paginator->previousPageUrl(),
            ],
            'data' => $dataPaginated->values(),
        ];
        return comman_custom_response($response);
    }

    public function deleteUserAccount(Request $request){
        $user_id = \Auth::user()->id;
        $user = User::where('id',$user_id)->first();
        if($user == null){
            $message = __('messages.user_not_found');__('messages.msg_fail_to_delete',['item' => __('messages.user')] );
            return comman_message_response($message,400);
        }
        $user->booking()->forceDelete();
        $user->payment()->forceDelete();
        $user->forceDelete();
        $message = __('messages.msg_deleted',['name' => __('messages.user')] );
        return comman_message_response($message,200);
    }
    public function deleteAccount(Request $request){
        $user_id = \Auth::user()->id;
        $user = User::where('id',$user_id)->first();
        if($user == null){
            $message = __('messages.user_not_found');__('messages.msg_fail_to_delete',['item' => __('messages.user')] );
            return comman_message_response($message,400);
        }
        if($user->user_type == 'provider'){
            if($user->providerPendingBooking()->count() == 0){
                $user->providerService()->forceDelete();
                $user->providerPendingBooking()->forceDelete();
                $provider_handyman = User::where('provider_id',$user_id)->get();
                if(count($provider_handyman) > 0){
                    foreach ($provider_handyman as $key => $value) {
                        $value->provider_id = NULL;
                        $value->update();
                    }
                }
                $user->forceDelete();
            }else{
                $message = __('messages.pending_booking');
                 return comman_message_response($message,402);
            }
        }else{
            if($user->handymanPendingBooking()->count() == 0){
                $user->handymanPendingBooking()->forceDelete();
                $user->forceDelete();
            }else{
                $message = __('messages.pending_booking');
                 return comman_message_response($message,402);
            }
        }
        $message = __('messages.msg_deleted',['name' => __('messages.user')] );
        return comman_message_response($message,200);
    }
    public function addUser(UserRequest $request)
    {
        $input = $request->all();

        $password = $input['password'];
        $input['display_name'] = $input['first_name']." ".$input['last_name'];
        $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
        $input['password'] = Hash::make($password);

        if( $input['user_type'] === 'provider')
        {
        }
        $user = User::create($input);
        $user->assignRole($input['user_type']);
        $input['api_token'] = $user->createToken('auth_token')->plainTextToken;

        unset($input['password']);
        $message = trans('messages.save_form',['form' => $input['user_type'] ]);
        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        $response = [
            'message' => $message,
            'data' => $user
        ];
        return comman_custom_response($response);
    }
    public function editUser(UserRequest $request)
    {
        if($request->has('id') && !empty($request->id)){
            $user = User::where('id',$request->id)->first();
        }
        if($user == null){
            return comman_message_response(__('messages.no_record_found'),400);
        }

        $user->fill($request->all())->update();

        if(isset($request->profile_image) && $request->profile_image != null ) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        $user_data = User::with(['country', 'state', 'city', 'providertype', 'handymantype'])->find($user->id);

        $message = __('messages.updated');
        $user_data['profile_image'] = getSingleMedia($user_data,'profile_image',null);
        $user_data['user_role'] = $user->getRoleNames();
        unset($user_data['roles']);
        unset($user_data['media']);
        $response = [
            'data' => $user_data,
            'message' => $message
        ];
        return comman_custom_response( $response );
    }
    public function userWalletBalance(Request $request){
        $user = Auth::user();
        $amount = 0;
        $wallet = Wallet::where('user_id',$user->id)->first();
        if($wallet !== null){
            $amount = $wallet->amount;
        }
        $response = [
            'balance' => $amount,
        ];
        return comman_custom_response( $response );
    }


    // user email verify
    public function verify(Request $request)
    {
        $email = $request->email;
        $user = User::where('email',$email)->first();
        if ($user === null) {
            $message = 'User not registered. Please check your email or register.';
            $response = [
                'message' => $message,
            ];
            return comman_custom_response($response);
        }
        if($user->is_email_verified == 0){
            $verificationLink = route('verify',['id' => $user->id]);
            $response_data=Mail::to($user->email)->send(new VerificationEmail($verificationLink));
            $message = 'Email Verification link has been sent to your email. Please Check your inbox';
            $response = [
                    'message' => $message,
                    'is_email_verified' => $user->is_email_verified,
            ];
            return comman_custom_response($response);

        }else{
            $message = 'Email already verify!!!';
            $response = [
                'message' => $message,
                'is_email_verified' => $user->is_email_verified,
        ];


        return comman_custom_response($response);
        }
    }
    public function checkUsername(Request $request)
    {
        $username = $request->input('username');

        // Check if username exists
        $exists = User::where('username', $username)->exists();
        if ($exists) {
            $message = __('messages.username_already_taken');
            return response()->json(['status' => 'error', 'message' => $message], 409); // 409 Conflict
        }

        return response()->json(['status' => 'success'], 200);
    }
    public function SwitchLang(Request $request)
    {
        $locale = $request->input('locale');
        App::setLocale($locale);
        session()->put('locale', $locale);
        \Artisan::call('cache:clear');
        if (auth()->check()) {
            $user = auth()->user();
            $user->language_option = $locale;
            $user->save();
        }
        return response()->json([
            'status' => true,
            'message' => __('messages.Language_preference_updated'),
            'locale' => $locale,
        ], 200);
    }

}

