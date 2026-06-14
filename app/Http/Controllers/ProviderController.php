<?php

namespace App\Http\Controllers;

use App\Http\Resources\API\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFavouriteService;
use App\Models\Plans;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Stripe;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\ProviderSlotMapping;
use App\Http\Requests\UserRequest;
use App\Models\ProviderPayout;
use App\Models\ProviderSubscription;
use App\Models\SubscriptionTransaction;
use App\Models\PaymentGateway;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Hash;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\CommissionEarning;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.providers' );
        if($request->status === 'pending'){
            $pageTitle = __('messages.pending_list_form_title',['form' => __('messages.provider')] );
        }
        if($request->status === 'subscribe'){
            $pageTitle = __('messages.list_form_title',['form' => __('messages.subscribe')] );
        }

        $auth_user = authSession();
        $assets = ['datatable'];
        $list_status = $request->status;
        return view('provider.index', compact('list_status','pageTitle','auth_user','assets','filter'));
    }

    public function getCitiesBaseOnCountry(Request $request)
    {
        $country_id = $request->query('country_id');

        if (!$country_id) {
            return response()->json(['error' => 'Country ID is required'], 400);
        }

        $state_ids = State::where('country_id', $country_id)->pluck('id');

        if ($state_ids->isEmpty()) {
            return response()->json([]);
        }
        $cities = City::whereIn('state_id', $state_ids)
                      ->select('id', 'name')
                      ->get();

        return response()->json($cities);
    }

    public function myindex(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.providers' );
        if($request->status === 'pending'){
            $pageTitle = __('messages.pending_list_form_title',['form' => __('messages.provider')] );
        }
        if($request->status === 'subscribe'){
            $pageTitle = __('messages.list_form_title',['form' => __('messages.subscribe')] );
        }

        $auth_user = authSession();
        $assets = ['datatable'];
        $list_status = $request->status;
        return view('provider.myindex', compact('list_status','pageTitle','auth_user','assets','filter'));
    }
    public function index_data(DataTables $datatable,Request $request)
    {
        $query = User::query();
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        $query = $query->where('user_type','provider');
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->withTrashed();
        }
        if($request->list_status == 'pending'){
            $query = $query->where('status',0);
        }else{
            $query = $query->where('status',1);
        }
        if($request->list_status == 'subscribe'){
            $query = $query->where('status',1)->where('is_subscribe',1);
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
            })

            ->editColumn('display_name', function ($query) {
                return view('provider.user', compact('query'));
            })
            ->editColumn('wallet', function ($query){
                return view('provider.wallet', compact('query'));
            })
            ->editColumn('status', function($query) {
                if($query->status == '0'){
                    $status = '<a class="btn-sm btn btn btn-success"  href='.route('provider.approve',$query->id).'><i class="fa fa-check"></i>Approve</a>';
                }else{
                    $status = '<span class="badge badge-active text-success bg-success-subtle">'.__('messages.active').'</span>';
                }
                return $status;
            })
            ->editColumn('providertype_id', function($query) {
                return ($query->providertype_id != null && isset($query->providertype)) ? $query->providertype->name : '-';
            })
            ->editColumn('address', function($query) {
                return ($query->address != null && isset($query->address)) ? $query->address : '-';
            })
            ->editColumn('created_at', function($query) {
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->created_at)) .'  '. date(optional($datetime)->time_format, strtotime($query->created_at))
                : $query->created_at;
                return $formattedDate;
            })

            ->filterColumn('providertype_id',function($query,$keyword){
                $query->whereHas('providertype',function ($q) use($keyword){
                    $q->where('name','like','%'.$keyword.'%');
                });
            })
            ->addColumn('action', function($provider){
                return view('provider.action',compact('provider'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','display_name','wallet','action','status'])
            ->toJson();
    }



    public function myindex_data(DataTables $datatable, Request $request)
{
    // Start the query with the user's favorite providers
    $query = User::query()
        ->whereHas('userFavourites', function ($subQuery) {
            $subQuery->where('user_id', auth()->user()->id);
        })
        ->list();

    $filter = $request->filter;

    if (isset($filter)) {
        if (isset($filter['column_status'])) {
            $query->where('status', $filter['column_status']);
        }
    }

    $query = $query->where('user_type', 'provider');

    if (auth()->user()->hasAnyRole(['admin'])) {
        $query->withTrashed();
    }

    if ($request->list_status == 'pending') {
        $query = $query->where('status', 0);
    } else {
        $query = $query->where('status', 1);
    }

    if ($request->list_status == 'subscribe') {
        $query = $query->where('status', 1)->where('is_subscribe', 1);
    }

    return $datatable->eloquent($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$row->id.'" name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.', this)">';
        })
        ->editColumn('display_name', function ($query) {
            return view('provider.user', compact('query'));
        })
        ->editColumn('status', function ($query) {
            if ($query->status == '0') {
                $status = '<a class="btn-sm text-white btn-success" href='.route('provider.approve', $query->id).'><i class="fa fa-check"></i>Approve</a>';
            } else {
                $status = '<span class="badge badge-active">'.__('messages.active').'</span>';
            }
            return $status;
        })
        ->editColumn('providertype_id', function ($query) {
            return ($query->providertype_id != null && isset($query->providertype)) ? $query->providertype->name : '-';
        })
        ->editColumn('address', function ($query) {
            return ($query->address != null && isset($query->address)) ? $query->address : '-';
        })
        ->editColumn('created_at', function ($query) {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

            $formattedDate = optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
                : $query->created_at;
            return $formattedDate;
        })
        ->filterColumn('providertype_id', function ($query, $keyword) {
            $query->whereHas('providertype', function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%');
            });
        })
        ->addIndexColumn()
        ->rawColumns(['check', 'display_name', 'status'])
        ->toJson();
}

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'change-status':
                $branches = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Provider Status Updated';
                break;

            case 'delete':
                User::whereIn('id', $ids)->delete();
                $message = 'Bulk Provider Deleted';
                break;

            case 'restore':
                User::whereIn('id', $ids)->restore();
                $message = 'Bulk Provider Restored';
                break;

            case 'permanently-delete':
                User::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Provider Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => __('messages.action_invalid')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('provider add')) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $id = $request->id;
        $auth_user = authSession();

        $providerdata = User::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.provider')]);

        if($providerdata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.provider')]);
            $providerdata = new User;
        }

        return view('provider.create', compact('pageTitle' ,'providerdata' ,'auth_user' ));
    }

    private function compressProfileImageForUpload($file): ?array
    {
        $maxDimension = 800;
        $maxPreparedSize = 120 * 1024;
        $qualities = [62, 52, 44, 36, 28];
        $originalMemoryLimit = ini_get('memory_limit');
        $sourceImage = null;
        $newImage = null;

        try {
            ini_set('memory_limit', '256M');

            $imageInfo = @getimagesize($file->getRealPath());
            if (!$imageInfo) {
                return null;
            }

            $originalWidth = (int) $imageInfo[0];
            $originalHeight = (int) $imageInfo[1];
            $mimeType = $imageInfo['mime'] ?? '';

            if (
                $mimeType === 'image/jpeg'
                && $file->getSize() <= $maxPreparedSize
                && max($originalWidth, $originalHeight) <= $maxDimension
            ) {
                return null;
            }

            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($file->getRealPath());
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($file->getRealPath());
                    break;
                case 'image/webp':
                    $sourceImage = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file->getRealPath()) : null;
                    break;
                default:
                    return null;
            }

            if (!$sourceImage) {
                return null;
            }

            $ratio = min($maxDimension / $originalWidth, $maxDimension / $originalHeight, 1);
            $newWidth = max(1, (int) round($originalWidth * $ratio));
            $newHeight = max(1, (int) round($originalHeight * $ratio));

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($newImage, 255, 255, 255);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $baseName) ?: 'provider-profile';
            $fileName = $safeName . '.jpg';
            $tempPath = tempnam(sys_get_temp_dir(), 'provider_profile_');
            $jpgPath = $tempPath . '.jpg';

            foreach ($qualities as $quality) {
                imagejpeg($newImage, $jpgPath, $quality);
                if (file_exists($jpgPath) && filesize($jpgPath) <= $maxPreparedSize) {
                    break;
                }
            }

            @unlink($tempPath);

            return [
                'path' => $jpgPath,
                'name' => $fileName,
            ];
        } catch (\Throwable $e) {
            Log::warning('Provider profile image compression failed: ' . $e->getMessage());
            return null;
        } finally {
            if ($sourceImage) {
                imagedestroy($sourceImage);
            }
            if ($newImage) {
                imagedestroy($newImage);
            }
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(UserRequest $request)
{
    $loginuser = \Auth::user();

    if(demoUserPermission()){
        return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
    }

    $data = $request->all();
    $id = $data['id'];
    $data['user_type'] = $data['user_type'] ?? 'provider';
    $data['is_featured'] = 0;

    if($request->has('is_featured')){
        $data['is_featured'] = 1;
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

    $data['display_name'] = $data['first_name']." ".$data['last_name'];

    // Handle why_choose_me field before creating/updating user
    if($request->has('title') || $request->has('about_description') || $request->has('reason')){
        $why_choose_me = [
            'title' => $request->title,
            'about_description' => $request->about_description,
            'reason' => isset($request->reason) ? array_filter($request->reason, function ($value) {
                return $value !== null;
            }) : null,
        ];
        $data['why_choose_me'] = json_encode($why_choose_me);
    }

    // Add these two lines to store tax_id and tax_country_id
    $data['tax_id'] = $request->tax_id;
    $data['tax_country_id'] = is_array($request->tax_id) ? $request->tax_id[0] : $request->tax_id;

    if($id == null){
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        $wallet = [
            'title' => $user->display_name,
            'user_id' => $user->id,
            'amount' => 0
        ];
        Wallet::create($wallet);

        // Keep parity with /register provider flow:
        // create a default free provider subscription on provider creation.
        if (!ProviderSubscription::where('user_id', $user->id)->exists()) {
            $startDate = now();
            $planType = 'weekly';

            $endDate = match ($planType) {
                'monthly' => $startDate->copy()->addMonth(),
                'yearly' => $startDate->copy()->addYear(),
                default => $startDate->copy()->addWeek(),
            };

            ProviderSubscription::create([
                'plan_id'         => 1,
                'user_id'         => $user->id,
                'title'           => 'Free plan',
                'identifier'      => 'free',
                'type'            => $planType,
                'start_at'        => $startDate,
                'end_at'          => $endDate,
                'amount'          => 0,
                'status'          => 'active',
                'payment_id'      => '1',
                'plan_limitation' => json_encode([
                    'featured_service' => ['is_checked' => null, 'limit' => null],
                    'handyman'         => ['is_checked' => null, 'limit' => null],
                    'service'          => ['is_checked' => null, 'limit' => null],
                ]),
                'duration'        => null,
                'description'     => 'Free plan',
                'plan_type'       => 'Free plan',
            ]);
        }
    } else {
        $user = User::findOrFail($id);
        $user->fill($data)->update();
    }

    if($data['status'] == 1 && auth()->user()->hasAnyRole(['admin'])){
        try {
            \Mail::send('verification.verification_email',
                [], function($message) use ($user)
                {
                    $message->from(env('MAIL_FROM_ADDRESS'));
                    $message->to($user->email);
                }
            );
        } catch (\Throwable $th) {
            // Handle exception or ignore
        }
    } 

    $user->assignRole($data['user_type']);
    if($request->hasFile('profile_image')){
        $compressedProfileImage = $this->compressProfileImageForUpload($request->file('profile_image'));

        try {
            if ($compressedProfileImage && file_exists($compressedProfileImage['path'])) {
                $user->clearMediaCollection('profile_image');
                $user->addMedia($compressedProfileImage['path'])
                    ->usingFileName($compressedProfileImage['name'])
                    ->toMediaCollection('profile_image');
            } else {
                storeMediaFile($user, $request->profile_image, 'profile_image');
            }
        } finally {
            if (!empty($compressedProfileImage['path']) && file_exists($compressedProfileImage['path'])) {
                @unlink($compressedProfileImage['path']);
            }
        }
    }

    $message = __('messages.update_form',[ 'form' => __('messages.provider') ]);
    if($user->wasRecentlyCreated){
        $message = __('messages.save_form',[ 'form' => __('messages.provider') ]);
    }

    if($user->providerTaxMapping()->count() > 0){
        $user->providerTaxMapping()->delete();
    }

    if($request->tax_id != null){
        foreach($request->tax_id as $tax){
            $provider_tax = [
                'provider_id' => $user->id,
                'tax_id' => $tax,
            ];
            $user->providerTaxMapping()->insert($provider_tax);
        }
    }

    // Handle tax_country_id for provider_taxes table
    if($request->has('tax_country_id')){
        \DB::table('provider_taxes')->updateOrInsert(
            ['provider_id' => $user->id],
            ['tax_id' => $request->tax_country_id]
        );
    }

    if($request->is('api/*')){
        return comman_message_response($message);
    }

    return redirect(route('provider.index'))->withSuccess($message);
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stripe()

    {

        return view('stripe');

    }
    public function stripePost(Request $request)
    {
        // Set Stripe API key
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Calculate the amount dynamically based on the request
        $amount = $request->plan_amount * 100; // Stripe expects the amount in cents

        try {
            // Create the Stripe charge
            $charge = Stripe\Charge::create([
                "amount" => $amount,   // Dynamic amount based on plan_amount
                "currency" => "EUR",   // Currency
                "source" => $request->stripeToken, // Stripe token from the request
                "description" => "Payment for plan: " . $request->plan_type, // Description with plan type
            ]);

            // Get the new plan details
            $newPlan = Plans::where('title', $request->plan_type)->first();
            if (!$newPlan) {
                return redirect()->back()->with('error', __('messages.plan_not_found'));
            }

            // Get current user's active subscription
            $user_id = auth()->id();
            $get_existing_plan = get_user_active_plan($user_id);
            
            $active_plan_left_days = 0;
            
            // Calculate remaining days from current plan
            if ($get_existing_plan) {
                $active_plan_left_days = check_days_left_plan($get_existing_plan, ['start_at' => now()]);
                
                // Deactivate existing plan if switching to different plan
                if ($request->plan_type != $get_existing_plan->plan_type) {
                    $existing_subscription = ProviderSubscription::where('user_id', $user_id)
                        ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
                        ->first();
                    if ($existing_subscription) {
                        $existing_subscription->update([
                            'status' => config('constant.SUBSCRIPTION_STATUS.INACTIVE')
                        ]);
                    }
                }
            }

            // Create new subscription following API logic
            $data = [
                'plan_id' => $newPlan->id,
                'user_id' => $user_id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'),
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, $active_plan_left_days, $newPlan->duration),
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
            ];

            $result = ProviderSubscription::create($data);

            if ($result) {
                // Create payment transaction
                $payment_data = [
                    'subscription_plan_id' => $result->id,
                    'user_id' => $result->user_id,
                    'amount' => $result->amount,
                    'payment_status' => 'paid',
                    'payment_type' => 'stripe',
                    'txn_id' => $charge->id,
                ];
                $payment = SubscriptionTransaction::create($payment_data);

                // Update subscription to active
                $result->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                $result->payment_id = $payment->id;
                $result->save();

                // Update user subscription status
                $user = User::find($user_id);
                $user->is_subscribe = 1;
                $user->save();

                Log::info('Subscription created successfully for user: ' . $user_id);
                return redirect()->back()->with('success', __('messages.subscription_upgraded'));
            }

            return redirect()->back()->with('error', __('messages.failed_to_create_subscription'));
        } catch (\Exception $e) {
            Log::error('Stripe charge failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
    public function upgradeFreePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required', // This is actually the subscription ID, not plan ID
            'plan_type' => 'required|string',
        ]);

        Log::info('Free plan upgrade request:', $request->all());

        // Get the new plan details
        $newPlan = Plans::where('title', $request->plan_type)->first();
        if (!$newPlan) {
            Log::error('Plan not found for type: ' . $request->plan_type);
            return response()->json(['error' => __('messages.plan_not_found')], 404);
        }

        Log::info('Found plan:', ['id' => $newPlan->id, 'title' => $newPlan->title, 'amount' => $newPlan->amount]);

        // Get current user's active subscription
        $user_id = auth()->id();
        
        // Find existing active subscription to update
        $existing_subscription = ProviderSubscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->first();

        if ($existing_subscription) {
            // Update existing subscription instead of creating new one
            $existing_subscription->update([
                'plan_id' => $newPlan->id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
                'status' => config('constant.SUBSCRIPTION_STATUS.ACTIVE'), // Free plan is immediately active
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration), // Reset end date for new plan
            ]);

            // Create payment transaction for free plan
            $payment_data = [
                'subscription_plan_id' => $existing_subscription->id,
                'user_id' => $existing_subscription->user_id,
                'amount' => $existing_subscription->amount,
                'payment_status' => 'paid',
                'payment_type' => 'free',
                'txn_id' => 'FREE_' . time(),
            ];
            $payment = SubscriptionTransaction::create($payment_data);

            // Update subscription with payment reference
            $existing_subscription->payment_id = $payment->id;
            $existing_subscription->save();

            // Update user subscription status
            $user = User::find($user_id);
            $user->is_subscribe = 1;
            $user->save();

            // Send subscription upgrade email
            sendSubscriptionUpgradeEmail($user, $existing_subscription, 'free', 'FREE_' . time());

            return response()->json(['status' => true, 'message' => __('messages.subscription_upgraded_free')]);
        } else {
            // If no existing subscription, create new one
            $data = [
                'plan_id' => $newPlan->id,
                'user_id' => $user_id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'status' => config('constant.SUBSCRIPTION_STATUS.ACTIVE'), // Free plan is immediately active
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration),
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
            ];

            $result = ProviderSubscription::create($data);

            if ($result) {
                // Create payment transaction for free plan
                $payment_data = [
                    'subscription_plan_id' => $result->id,
                    'user_id' => $result->user_id,
                    'amount' => $result->amount,
                    'payment_status' => 'paid',
                    'payment_type' => 'free',
                    'txn_id' => 'FREE_' . time(),
                ];
                $payment = SubscriptionTransaction::create($payment_data);

                // Update subscription with payment reference
                $result->payment_id = $payment->id;
                $result->save();

                // Update user subscription status
                $user = User::find($user_id);
                $user->is_subscribe = 1;
                $user->save();

                // Send subscription upgrade email
                sendSubscriptionUpgradeEmail($user, $result, 'free', 'FREE_' . time());

                return response()->json(['status' => true, 'message' => __('messages.subscription_created')]);
            }
        }

        return response()->json(['error' => 'Subscription upgrade failed.'], 500);
    }

    public function walletPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
            'plan_type' => 'required|string',
            'plan_amount' => 'required|numeric',
        ]);

        // Get the new plan details
        $newPlan = Plans::where('title', $request->plan_type)->first();
        if (!$newPlan) {
            return response()->json(['status' => false, 'message' => __('messages.plan_not_found')], 404);
        }

        $user_id = auth()->id();
        $user = User::find($user_id);
        
        // Check wallet balance
        $wallet = $user->wallet;
        if (!$wallet || $wallet->amount < $request->plan_amount) {
            return response()->json(['status' => false, 'message' => __('messages.insufficient_wallet_balance')], 400);
        }

        // Find existing active subscription to update
        $existing_subscription = ProviderSubscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->first();

        if ($existing_subscription) {
            // Update existing subscription instead of creating new one
            $existing_subscription->update([
                'plan_id' => $newPlan->id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
                'status' => config('constant.SUBSCRIPTION_STATUS.ACTIVE'),
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration), // Reset end date for new plan
            ]);

            // Create payment transaction
            $payment_data = [
                'subscription_plan_id' => $existing_subscription->id,
                'user_id' => $existing_subscription->user_id,
                'amount' => $existing_subscription->amount,
                'payment_status' => 'paid',
                'payment_type' => 'wallet',
                'txn_id' => 'WALLET_' . time(),
            ];
            $payment = SubscriptionTransaction::create($payment_data);

            // Update subscription with payment reference
            $existing_subscription->payment_id = $payment->id;
            $existing_subscription->save();

            // Deduct from wallet
            $wallet->amount -= $request->plan_amount;
            $wallet->save();

            // Update user subscription status
            $user->is_subscribe = 1;
            $user->save();

            // Send subscription upgrade email
            sendSubscriptionUpgradeEmail($user, $existing_subscription, 'wallet', 'WALLET_' . time());

            return response()->json(['status' => true, 'message' => __('messages.subscription_upgrade_wallet')]);
        } else {
            // If no existing subscription, create new one
            $data = [
                'plan_id' => $newPlan->id,
                'user_id' => $user_id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'),
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration),
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
            ];

            $result = ProviderSubscription::create($data);

            if ($result) {
                // Create payment transaction
                $payment_data = [
                    'subscription_plan_id' => $result->id,
                    'user_id' => $result->user_id,
                    'amount' => $result->amount,
                    'payment_status' => 'paid',
                    'payment_type' => 'wallet',
                    'txn_id' => 'WALLET_' . time(),
                ];
                $payment = SubscriptionTransaction::create($payment_data);

                // Update subscription to active
                $result->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                $result->payment_id = $payment->id;
                $result->save();

                // Deduct from wallet
                $wallet->amount -= $request->plan_amount;
                $wallet->save();

                // Update user subscription status
                $user->is_subscribe = 1;
                $user->save();

                // Send subscription upgrade email
                sendSubscriptionUpgradeEmail($user, $result, 'wallet', 'WALLET_' . time());

                return response()->json(['status' => true, 'message' => __('messages.subscription_created_wallet')]);
            }
        }

        return response()->json(['status' => false, 'message' => __('messages.failed_to_update_subscription')], 500);
    }

    public function paypalCreate(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
            'plan_type' => 'required|string',
            'plan_amount' => 'required|numeric',
        ]);

        // Get the new plan details
        $newPlan = Plans::where('title', $request->plan_type)->first();
        if (!$newPlan) {
            return response()->json(['status' => false, 'message' => __('messages.plan_not_found')], 404);
        }

        // Create PayPal payment session
        try {
            $paypalController = app(\App\Http\Controllers\PayPalController::class);
            $paypalRequest = new Request([
                'amount' => $request->plan_amount,
                'plan_type' => $request->plan_type,
                'plan_id' => $request->plan_id
            ]);
            
            $response = $paypalController->createSubscriptionPayment($paypalRequest);
            $responseData = json_decode($response->getContent(), true);
            
            // PayPal controller returns 'url' on success or 'error' on failure
            if (isset($responseData['url'])) {
                return response()->json([
                    'status' => true,
                    'url' => $responseData['url'],
                    'message' => __('messages.paypal_payment_initiated')
                ]);
            } elseif (isset($responseData['error'])) {
                return response()->json(['status' => false, 'message' => $responseData['error']], 500);
            }
            
            return response()->json(['status' => false, 'message' => __('messages.failed_to_create_paypal')], 500);
        } catch (\Exception $e) {
            Log::error('PayPal subscription payment failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'PayPal payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function bankTransfer(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
            'plan_type' => 'required|string',
            'plan_amount' => 'required|numeric',
        ]);

        // Get the new plan details
        $newPlan = Plans::where('title', $request->plan_type)->first();
        if (!$newPlan) {
            return response()->json(['status' => false, 'message' => __('messages.plan_not_found')], 404);
        }

        $user_id = auth()->id();
        
        // Find existing active subscription to update
        $existing_subscription = ProviderSubscription::where('user_id', $user_id)
            ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
            ->first();

        if ($existing_subscription) {
            // Update existing subscription instead of creating new one
            $existing_subscription->update([
                'plan_id' => $newPlan->id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
                'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'), // Bank transfer starts as pending
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration), // Reset end date for new plan
            ]);

            // Create payment transaction with pending status
            $payment_data = [
                'subscription_plan_id' => $existing_subscription->id,
                'user_id' => $existing_subscription->user_id,
                'amount' => $existing_subscription->amount,
                'payment_status' => 'pending',
                'payment_type' => 'bank_transfer',
                'txn_id' => 'BANK_' . time(),
            ];
            $payment = SubscriptionTransaction::create($payment_data);

            // Update subscription with payment reference
            $existing_subscription->payment_id = $payment->id;
            $existing_subscription->save();

            // Send bank transfer instructions email
            $user = User::find($user_id);
            sendBankTransferConfirmationEmail($user, $existing_subscription, $payment);

            return response()->json(['status' => true, 'message' => __('messages.subscription_upgrade_pending_payment')]);
        } else {
            // If no existing subscription, create new one
            $data = [
                'plan_id' => $newPlan->id,
                'user_id' => $user_id,
                'title' => $newPlan->title,
                'identifier' => $newPlan->identifier,
                'type' => $newPlan->type,
                'amount' => $newPlan->amount,
                'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'),
                'start_at' => now(),
                'end_at' => get_plan_expiration_date(now(), $newPlan->type, 0, $newPlan->duration),
                'duration' => $newPlan->duration,
                'description' => $newPlan->description,
                'plan_type' => $newPlan->plan_type,
                'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
            ];

            $result = ProviderSubscription::create($data);

            if ($result) {
                // Create payment transaction with pending status
                $payment_data = [
                    'subscription_plan_id' => $result->id,
                    'user_id' => $result->user_id,
                    'amount' => $result->amount,
                    'payment_status' => 'pending',
                    'payment_type' => 'bank_transfer',
                    'txn_id' => 'BANK_' . time(),
                ];
                $payment = SubscriptionTransaction::create($payment_data);

                // Update subscription with payment reference
                $result->payment_id = $payment->id;
                $result->save();

                // Send bank transfer instructions email
                $user = User::find($user_id);
                sendBankTransferConfirmationEmail($user, $result, $payment);

                return response()->json(['status' => true, 'message' => __('messages.subscription_created_pending_payment')]);
            }
        }

        return response()->json(['status' => false, 'message' => __('messages.failed_to_update_subscription')], 500);
    }

 

        public function getCountries()
        {
            $countries = Country::select('id', 'name')->get();
            return response()->json($countries);
        }














    public function show($id, $withdrawAmount = 0)
{
    $auth_user = authSession();

    if ($id != auth()->user()->id && !auth()->user()->hasRole(['admin', 'demo_admin'])) {
        return redirect(route('home'))->withErrors(trans('messages.demo_permission_denied'));
    }
    $providerdata = User::with('providerDocument', 'booking','commission_earning')
                        ->where('user_type', 'provider')
                        ->where('id', $id)
                        ->first();

    $data = Booking::where('provider_id', $id)->selectRaw(
        'COUNT(CASE WHEN status = "pending" THEN "pending" END) AS PendingStatusCount,
        COUNT(CASE WHEN status = "in_progress"  THEN "InProgress" END) AS InProgressstatuscount,
        COUNT(CASE WHEN status = "completed"  THEN "Completed" END) AS Completedstatuscount,
        COUNT(CASE WHEN status = "accept"  THEN "Accepted" END) AS Acceptedstatuscount,
        COUNT(CASE WHEN status = "on_going"  THEN "Ongoing" END) AS Ongoingstatuscount'
    )->first()->toArray() ?? null;
   $totalbooking = Booking::where('provider_id', $id)->count();
    $providerPayout = ProviderPayout::where('provider_id',$id)->sum('amount') ?? 0;
    $commissionData = null;
    if($providerdata !== null){
        $commissionData = $providerdata->commission_earning()
        ->whereHas('getbooking', function ($query) {
            $query->where('status', 'completed');
        })
        ->where('commission_status', 'unpaid')
        ->where('user_type', 'provider')
        ->pluck('booking_id');
        $ProviderEarning = 0;

            if ($commissionData->isNotEmpty()) {
                // Fetch all unpaid commissions for the relevant bookings in a single query
                $ProviderEarning = CommissionEarning::whereIn('booking_id', $commissionData)
                    ->whereIn('user_type', ['provider', 'handyman'])
                    ->where('commission_status', 'unpaid')
                    ->sum('commission_amount'); // Directly sum the commission_amount
            }
    }else {
        $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
        return redirect(route('provider.index'))->withError($msg);
    }

    $commissionAmount = $ProviderEarning ? $ProviderEarning : 0;
    $alreadyWithdrawn = $providerPayout;
    $totalAmount = $alreadyWithdrawn + $commissionAmount;
    $wallet = $providerdata ? optional($providerdata->wallet)->amount : 0;

    $providerData = [
        'wallet' => $wallet,
        'providerAlreadyWithdrawAmt' => $alreadyWithdrawn,
        'pendWithdrwan' => $commissionAmount,
        'totalAmount' => $totalAmount,
        'total_booking' => $totalbooking,
    ];

    $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);

    return view('provider.view', compact('pageTitle', 'providerdata', 'auth_user', 'data',  'providerPayout', 'providerData','totalAmount'));
}



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $provider = User::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.provider')] );

        if($provider != '') {
            $provider->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.provider')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }
    public function action(Request $request){
        $id = $request->id;

        $provider  = User::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.provider')] );
        if($request->type == 'restore') {
            $provider->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.provider')] );
        }

        if($request->type === 'forcedelete'){
            $provider->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.provider')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
    public function bankDetails(ServiceDataTable $dataTable, Request $request)
    {
        $auth_user = authSession();
        $providerdata = User::with('getServiceRating')->where('user_type', 'provider')->where($request->id)->first();
        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return $dataTable
            ->with('provider_id', $request->id)
            ->render('provider.bank-details', compact('pageTitle', 'providerdata', 'auth_user'));
    }

    public function review(Request $request, $id)
    {
        $auth_user = authSession();
        if ($id != auth()->user()->id && !auth()->user()->hasRole(['admin', 'demo_admin'])) {
            return redirect(route('home'))->withErrors(trans('messages.demo_permission_denied'));
        }
        $providerdata = User::with('getServiceRating')->where('user_type', 'provider')->where('id', $id)->first();
        $earningData = array();
        $time_zone=getTimeZone();

        foreach ($providerdata->getServiceRating as $bookingreview) {

            $booking_id = $bookingreview->id;
            // $date = optional($bookingreview->booking)->date ?? '-';
            $date = $bookingreview->updated_at->timezone($time_zone) ?? '-';
            $updated_at = $bookingreview->updated_at;
            $rating = $bookingreview->rating;
            $review = $bookingreview->review;
            $user_name = optional($bookingreview->customer)->first_name .' '. optional($bookingreview->customer)->last_name;
            $earningData[] = [
                'booking_id'=>$booking_id,
                'date' => $date,
                'rating' => $rating,
                'review' => $review ?? '-',
                'user_name' => $user_name ?? '-',
                'updated_at' => date('Y-m-d H:i:s', strtotime($updated_at)),
            ];
        }

        if ($request->ajax()) {
            return Datatables::of($earningData)
                ->addIndexColumn()
                ->editColumn('date', function ($row) {
                    if (is_array($row)) {
                        $row = (object)$row;
                    }
                    $startAt = isset($row->date) ? $row->date : null;
                    if ($startAt !== null) {
                        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                        $date = optional($datetime)->date_format && optional($datetime)->time_format
                        ? date(optional($datetime)->date_format, strtotime($startAt)) .'  '. date(optional($datetime)->time_format, strtotime($startAt))
                        : $startAt;
                        return $date;
                    }
                    return null;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return view('provider.review', compact('pageTitle','earningData', 'auth_user', 'providerdata'));
    }
    public function providerDetail(Request $request)
    {

        $tabpage = $request->tabpage;
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.service')]);
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $user_data = User::find($user_id);
        $earningData = array();
        $payment_data = PaymentGateway::where('type', $tabpage)->first();
        $provideId = $request->providerId;
        $plandata = ProviderSubscription::where('user_id',$request->providerid)->orderBy('id', 'desc');
        if($request->tabpage == 'subscribe-plan'){
            $plandata = $plandata->where('plan_type','subscribe');
        }if($request->tabpage == 'unsubscribe-plan'){
            $plandata = $plandata->where('plan_type','unsubscribe');
        }
        switch ($tabpage) {
            case 'all-plan':

                if ($request->ajax() && $request->type == 'tbl') {
                 return  Datatables::of($plandata)
                   ->addIndexColumn()
                   ->rawColumns([])
                   ->make(true);
                }

               return view('providerdetail.all-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));
                break;
            case 'subscribe-plan':
                if ($request->ajax() && $request->type == 'tbl') {
                    return  Datatables::of($plandata)
                      ->addIndexColumn()
                      ->rawColumns([])
                      ->make(true);
                   }
                   return view('providerdetail.subscribe-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));

                break;
            case 'unsubscribe-plan':
                if ($request->ajax() && $request->type == 'tbl') {
                    return  Datatables::of($plandata)
                      ->addIndexColumn()
                      ->rawColumns([])
                      ->make(true);
                   }
                   return view('providerdetail.unsubscribe-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));

                break;
            default:
                $data  = view('providerdetail.' . $tabpage, compact('tabpage', 'auth_user', 'payment_data'))->render();
                break;
        }

       return response()->json($data);
    }

    public function approve($id){
        $provider = User::find($id);
        $provider->status = 1;
        $provider->save();
        $msg = __('messages.approve_successfully');
        return redirect()->back()->withSuccess($msg);
    }

    public function getChangePassword(Request $request){
        $id = $request->id;
        $auth_user = authSession();

        $providerdata = User::find($id);
        $pageTitle = __('messages.change_password',['form'=> __('messages.change_password')]);
        return view('provider.changepassword', compact('pageTitle' ,'providerdata' ,'auth_user'));
    }

    public function changePassword(Request $request)
    {
        if (demoUserPermission()) {
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $user = User::where('id', $request->id)->first();

        if ($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }

        $validator = \Validator::make($request->all(), [
            'old' => 'required|min:8|max:255',
            'password' => 'required|min:8|confirmed|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('password')) {
                $message = __('messages.confirmed',['name' => __('messages.password')]);
                return redirect()->route('provider.changepassword', ['id' => $user->id])->with('error', $message);
            }
            return redirect()->route('provider.changepassword', ['id' => $user->id])->with('errors', $validator->errors());
        }

        $hashedPassword = $user->password;

        $match = Hash::check($request->old, $hashedPassword);

        $same_exits = Hash::check($request->password, $hashedPassword);
        if ($match) {
            if ($same_exits) {
                $message = __('messages.old_new_pass_same');
                return redirect()->route('provider.changepassword',['id' => $user->id])->with('error', $message);
            }

            $user->fill([
                'password' => Hash::make($request->password)
            ])->save();
            $message = __('messages.password_change');
            return redirect()->route('provider.index')->withSuccess($message);
        } else {
            $message = __('messages.valid_password');
            return redirect()->route('provider.changepassword',['id' => $user->id])->with('error', $message);
        }
    }

    public function getPlans()
{
    $plans = Plans::select('id', 'title', 'amount')->where('status',1)->get();
    return response()->json($plans);
}


public function getProviderTimeSlot(Request $request)
{
    $auth_user = authSession();
    
    // Set default provider_id to current user if not provided
    $provider_id = $request->id ?? auth()->user()->id;

    // Permission check: only allow if viewing own slots or user is admin
    if ($provider_id != auth()->user()->id && !auth()->user()->hasRole(['admin', 'demo_admin'])) {
        return redirect(route('home'))->withErrors(trans('messages.demo_permission_denied'));
    }

    $providerdata = User::with('providerslotsmapping')
        ->where('user_type', 'provider')
        ->where('id', $provider_id)
        ->first();

    // Set timezone
    $admin = User::where('user_type', 'admin')->first();
    date_default_timezone_set($admin->time_zone ?? 'UTC');

    // Fetch date-based slots for calendar view
    $startDate = \Carbon\Carbon::now();
    $endDate = \Carbon\Carbon::now()->addDays(90); // Show 3 months ahead

    $slots = ProviderSlotMapping::where('provider_id', $provider_id)
        ->whereNotNull('date')
        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->orderBy('date', 'asc')
        ->orderBy('start_at', 'asc')
        ->get();

    // Get unique dates that have slots (for marking green in calendar)
    // Use array_values() to ensure it's a numeric array (not associative) for JSON encoding
    $datesWithSlots = array_values($slots->pluck('date')->unique()->toArray());

    // Group slots by date for display
    $calendarSlots = [];
    foreach ($slots as $slot) {
        $date = $slot->date;
        $timeSlot = $slot->start_at;
        if (strlen($timeSlot) == 5) {
            $timeSlot .= ':00';
        }
        
        if (!isset($calendarSlots[$date])) {
            $calendarSlots[$date] = [];
        }
        $calendarSlots[$date][] = $timeSlot;
    }

    // Remove duplicates and sort
    foreach ($calendarSlots as $date => $times) {
        $calendarSlots[$date] = array_unique($times);
        sort($calendarSlots[$date]);
    }

    $pageTitle = __('messages.slot', ['form' => __('messages.slot')]);

    return view('provider.timeslot', compact(
        'auth_user',
        'pageTitle',
        'provider_id',
        'providerdata',
        'datesWithSlots',
        'calendarSlots'
    ));
}


    public function editProviderTimeSlot(Request $request){
        $auth_user = authSession();
        
        // Set default provider_id to current user if not provided
        $provider_id = $request->id ?? auth()->user()->id;
        
        // Permission check: only allow if editing own slots or user is admin
        if ($provider_id != auth()->user()->id && !auth()->user()->hasRole(['admin', 'demo_admin'])) {
            return redirect(route('provider.time-slot', ['id' => auth()->user()->id]))->withErrors(trans('messages.demo_permission_denied'));
        }
        
        $providerdata = User::with('providerslotsmapping')->where('user_type','provider')->where('id',$provider_id)->first();
        
        // Set timezone
        $admin = User::where('user_type', 'admin')->first();
        date_default_timezone_set($admin->time_zone ?? 'UTC');
        $selectedDate = $request->date; // Get date from query parameter if provided

        // Fetch date-based slots like the API does
        $startDate = \Carbon\Carbon::now();
        $endDate = \Carbon\Carbon::now()->addDays(90); // Show 3 months ahead

        $slots = ProviderSlotMapping::where('provider_id', $provider_id)
            ->whereNotNull('date')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->orderBy('start_at', 'asc')
            ->get();

        // Group slots by date for the calendar
        $calendarSlots = [];
        foreach ($slots as $slot) {
            $date = $slot->date;
            $timeSlot = $slot->start_at;
            if (strlen($timeSlot) == 5) {
                $timeSlot .= ':00';
            }
            
            if (!isset($calendarSlots[$date])) {
                $calendarSlots[$date] = [];
            }
            $calendarSlots[$date][] = $timeSlot;
        }

        // Remove duplicates and sort
        foreach ($calendarSlots as $date => $times) {
            $calendarSlots[$date] = array_unique($times);
            sort($calendarSlots[$date]);
        }

        $pageTitle = __('messages.slot', ['form' => __('messages.slot')]);

        return view('provider.edittimeslot', compact('auth_user', 'pageTitle', 'provider_id', 'calendarSlots', 'providerdata', 'selectedDate'));
    }
    public function createSubscriptionStripePayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
            'plan_type' => 'required|string',
            'plan_amount' => 'required|numeric',
        ]);

        $user_id = auth()->id();
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $payAmount = (float)$request->plan_amount;

        if ($payAmount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid amount'], 422);
        }

        // Get Stripe settings
        $payment_geteway_value = getPaymentMethodkey('stripe');
        $stripe_secret = $payment_geteway_value['stripe_key'] ?? null;

        // Fallback to environment variables if database configuration is not available
        if (!$stripe_secret) {
            $stripe_secret = env('STRIPE_SECRET');
            Log::info('Using Stripe secret from environment variables');
        }

        if (!$stripe_secret) {
            Log::error('Stripe secret key not found in payment gateway settings or environment variables');
            return response()->json(['status' => false, 'message' => 'Stripe not configured'], 500);
        }

        Log::info('Stripe secret key found, creating Stripe client');

        $stripe = new \Stripe\StripeClient($stripe_secret);

        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
        // Resolve currency code dynamically from default_currency (Country)
        try {
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string)($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }

        Log::info('Currency code set to EUR (forced)');

        $baseURL = 'https://frobster.com';

        //dd($baseURL);
        
        if (empty($baseURL)) {
            return response()->json(['status' => false, 'message' => 'APP_URL not configured'], 500);
        }

        // Ensure base URL doesn't end with slash
        $baseURL = rtrim($baseURL, '/');

        Log::info('Base URL determined: ' . $baseURL);
        Log::info('Creating Stripe session for user: ' . $user_id . ' with plan: ' . $request->plan_type . ' amount: ' . $payAmount);

        try {
            $successUrl = $baseURL . '/subscription/stripe/save/' . $user_id .
                '?plan_type=' . urlencode($request->plan_type) . '&session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseURL . '/provider_info/' . $user_id;
            
            Log::info('Stripe URLs - Success: ' . $successUrl . ', Cancel: ' . $cancelUrl);
            
            $unitAmount = stripe_unit_amount_from_decimal($payAmount, $currencyCode);
            Log::info('Unit amount calculated: ' . $unitAmount . ' for amount: ' . $payAmount . ' currency: ' . $currencyCode);
            
            $session = $stripe->checkout->sessions->create([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'payment_method_types' => ['card'],
                'billing_address_collection' => 'required',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currencyCode,
                        'product_data' => [
                            'name' => 'Subscription Plan: ' . $request->plan_type,
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
            ]);

            return response()->json(['status' => true, 'id' => $session->id, 'url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveSubscriptionStripePayment(Request $request, $id)
    {
       // dd($request->all());
        $user_id = $id;
       // dd($user_id);
        $session_id = $request->get('session_id');
        $plan_type = $request->get('plan_type');
    
        if (!$session_id || !$plan_type) {
            return redirect()->route('provider_info', $user_id)->with('error', 'Invalid payment session.');
        }
    
        // Get Stripe settings
        $payment_geteway_value = getPaymentMethodkey('stripe');
        $stripe_secret = $payment_geteway_value['stripe_key'] ?? null;
    
        if (!$stripe_secret) {
            return redirect()->route('provider_info', $user_id)->with('error', 'Stripe not configured.');
        }
    
        $stripe = new \Stripe\StripeClient($stripe_secret);
    
        try {
            // Retrieve the session to get payment details
            $session = $stripe->checkout->sessions->retrieve($session_id);
    
            if ($session->payment_status === 'paid') {
                // Get the new plan details
                $newPlan = Plans::where('title', $plan_type)->first();
                if (!$newPlan) {
                    return redirect()->route('provider_info', $user_id)->with('error', __('messages.plan_not_found'));
                }
    
                // Get current user's active subscription
                $get_existing_plan = get_user_active_plan($user_id);
                $active_plan_left_days = 0;
    
                if ($get_existing_plan) {
                    $active_plan_left_days = check_days_left_plan($get_existing_plan, ['start_at' => now()]);
                }
    
                // Prepare subscription data
                $data = [
                    'plan_id' => $newPlan->id,
                    'user_id' => $user_id,
                    'title' => $newPlan->title,
                    'identifier' => $newPlan->identifier,
                    'type' => $newPlan->type,
                    'amount' => $newPlan->amount,
                    'status' => config('constant.SUBSCRIPTION_STATUS.PENDING'),
                    'start_at' => now(),
                    'end_at' => get_plan_expiration_date(now(), $newPlan->type, $active_plan_left_days, $newPlan->duration),
                    'duration' => $newPlan->duration,
                    'description' => $newPlan->description,
                    'plan_type' => $newPlan->plan_type,
                    'plan_limitation' => $newPlan->planlimit ? json_encode($newPlan->planlimit->plan_limitation) : null,
                ];
    
                // Update existing subscription if found, otherwise create new
                $existing_subscription = ProviderSubscription::where('user_id', $user_id)
                    ->where('status', config('constant.SUBSCRIPTION_STATUS.ACTIVE'))
                    ->first();
    
                if ($existing_subscription) {
                    $existing_subscription->update($data);
                    $result = $existing_subscription;
                } else {
                    $result = ProviderSubscription::create($data);
                }
    
                if ($result) {
                    // Create payment transaction
                    $payment_data = [
                        'subscription_plan_id' => $result->id,
                        'user_id' => $result->user_id,
                        'amount' => $result->amount,
                        'payment_status' => 'paid',
                        'payment_type' => 'stripe',
                        'txn_id' => $session->payment_intent,
                    ];
                    $payment = SubscriptionTransaction::create($payment_data);
    
                    // Update subscription to active
                    $result->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                    $result->payment_id = $payment->id;
                    $result->save();
    
                    // Update user subscription status
                    $user = User::find($user_id);
                    $user->is_subscribe = 1;
                    $user->save();

                    // Send subscription upgrade email
                    sendSubscriptionUpgradeEmail($user, $result, 'stripe', $session->payment_intent);

                    return redirect()->route('provider_info', $user_id)->with('success', 'Subscription updated successfully!');
                }
    
                return redirect()->route('provider_info', $user_id)->with('error', 'Failed to save subscription.');
            } else {
                return redirect()->route('provider_info', $user_id)->with('error', 'Payment was not completed.');
            }
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed: ' . $e->getMessage());
            return redirect()->route('provider_info', $user_id)->with('error', 'Payment verification failed.');
        }
    }
    
    public function getSubCategoryList(Request $request){
        $subcategory = SubCategory::where('status',1);
        if(auth()->user() !== null){
            if(auth()->user()->hasRole('admin')){
                $subcategory = new SubCategory();
                $subcategory = $subcategory->withTrashed();
            }
        }

        if($request->has('cat_id')){
            $subcategory->where('category_id',$request->cat_id);
        }

        $subcategory = $subcategory->orderBy('name','asc')->get();

        return response()->json($subcategory);
    }
}
