<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\Models\SubscriptionTransaction;
use App\Models\Plans;
use App\Http\Resources\API\ProviderSubscribeResource;
use App\Http\Requests\ProviderSubscriptionRequest; 
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    use NotificationTrait;
    public function providerSubscribe(ProviderSubscriptionRequest $request)
    {
        $user_id = $request->user_id ?? auth()->id();
        $user = User::find($user_id);
    
        date_default_timezone_set(getTimeZone());
    
        // Base request data
        $data = $request->all();
        $data['user_id'] = $user_id;
        $data['status'] = config('constant.SUBSCRIPTION_STATUS.PENDING');
        $data['start_at'] = now()->format('Y-m-d H:i:s');  // Always from today
    
        /*
        |--------------------------------------------------------------------------
        | 1) Resolve Plan (Priority: plan_id → identifier → plan_type → title)
        |--------------------------------------------------------------------------
        */
        $plan = null;
    
        if ($request->filled('plan_id')) {
            $plan = Plans::find($request->plan_id);
        }
    
        if (!$plan && $request->filled('identifier')) {
            $plan = Plans::where('identifier', $request->identifier)->first();
        }
    
        if (!$plan && $request->filled('plan_type')) {
            $plan = Plans::where('title', $request->plan_type)->first();
        }
    
        if (!$plan && $request->filled('title')) {
            $plan = Plans::where('title', $request->title)->first();
        }
    
        /*
        |--------------------------------------------------------------------------
        | 2) Inject Plan Defaults If Found
        |--------------------------------------------------------------------------
        */
        if ($plan) {
            $data['plan_id'] = $plan->id;
            $data['title'] = $plan->title;
            $data['identifier'] = $plan->identifier;
            $data['type'] = $plan->type;                  // daily/weekly/monthly/yearly
            $data['duration'] = $plan->duration;          // integer duration
            $data['amount'] = $plan->amount;
            $data['description'] = $plan->description;
            $data['plan_type'] = $plan->plan_type;        // Silver, Gold, Free, etc.
            $data['plan_limitation'] = $plan->planlimit 
                ? json_encode($plan->planlimit->plan_limitation) 
                : null;
        }
    
        /*
        |--------------------------------------------------------------------------
        | 3) Allow Client to Override type & duration (optional)
        |--------------------------------------------------------------------------
        */
        if ($request->filled('type')) {
            $data['type'] = strtolower($request->type);
        }
    
        if ($request->filled('duration')) {
            $data['duration'] = (int) $request->duration;
        }
    
        /*
        |--------------------------------------------------------------------------
        | 4) Check If User has an Active Plan (for leftover days)
        |--------------------------------------------------------------------------
        */
        $existing_plan = get_user_active_plan($user_id);
        $active_plan_left_days = 0;
    
        if ($existing_plan) {
    
            // Calculate remaining active days
            $active_plan_left_days = check_days_left_plan($existing_plan, $data);
    
            // If user is switching plan → mark old plan inactive
            if ($request->identifier != $existing_plan->identifier) {
                $existing_plan->update([
                    'status' => config('constant.SUBSCRIPTION_STATUS.INACTIVE')
                ]);
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | 5) Calculate Auto End Date Using Existing Helper
        |--------------------------------------------------------------------------
        */
        $effectiveType = strtolower($data['type'] ?? 'monthly');
        $effectiveDuration = (int) ($data['duration'] ?? 1);
    
        $data['end_at'] = get_plan_expiration_date(
            $data['start_at'],          // always now/today
            $effectiveType,             // monthly/weekly/yearly
            $active_plan_left_days,     // leftover days
            $effectiveDuration          // duration
        );
    
        /*
        |--------------------------------------------------------------------------
        | 6) Normalize plan_limitation (ensure encoded)
        |--------------------------------------------------------------------------
        */
        if (!empty($data['plan_limitation']) && is_string($data['plan_limitation'])) {
            $data['plan_limitation'] = json_decode($data['plan_limitation'], true);
        }
        
    
        /*
        |--------------------------------------------------------------------------
        | 7) Create or Update Subscription
        |--------------------------------------------------------------------------
        */
        $existing_subscription = ProviderSubscription::where('user_id', $user_id)->first();
    
        if ($existing_subscription) {
            $existing_subscription->fill($data)->save();
            $subscription = $existing_subscription;
        } else {
            $subscription = ProviderSubscription::create($data);
        }
    
        /*
        |--------------------------------------------------------------------------
        | 8) Process Payment (store transaction)
        |--------------------------------------------------------------------------
        */
        if ($subscription) {
    
            $payment_data = [
                'subscription_plan_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'amount' => $subscription->amount,
                'payment_status' => $request->payment_status,
                'payment_type' => $request->payment_type,
            ];
    
            $payment = SubscriptionTransaction::create($payment_data);
    
            if ($payment->payment_status == 'paid') {
                $subscription->status = config('constant.SUBSCRIPTION_STATUS.ACTIVE');
                $subscription->payment_id = $payment->id;
                $subscription->save();
    
                $user->is_subscribe = 1;
                $user->save();
    
                $message = __('messages.payment_completed');
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | 9) Send Notifications
        |--------------------------------------------------------------------------
        */
        $items = new ProviderSubscribeResource($subscription);
        $response = ['data' => $items];
    
        $activity_data = [
            'activity_type' => 'subscription_add',
            'subscription_data' => $subscription,
        ];
    
        $this->sendNotification($activity_data);
    
        return comman_custom_response($response);
    }
    

public function cancelSubscription(Request $request)
{
    $user_id = $request->user_id ? $request->user_id : auth()->id();
    $plan_id  = $request->id;

    $provider_subscription = ProviderSubscription::where('id', $plan_id)
        ->where('user_id', $user_id)
        ->first();

    $user = User::where('id', $user_id)->first();

    if ($provider_subscription) {
        date_default_timezone_set(getTimeZone());

        $start_date = now(); // Current date-time
        $end_date = $start_date->copy()->addWeek(); // Add 7 days for weekly plan

        $provider_subscription->status = 'active';
        $provider_subscription->title = 'Free plan';
        $provider_subscription->plan_type = 'Free plan';
        $provider_subscription->type = 'weekly';
        $provider_subscription->amount = 0;
        $provider_subscription->start_at = $start_date;
        $provider_subscription->end_at = $end_date; // ✅ Set calculated end date

        $plan_limitation = [
            'service' => ['limit' => null],
            'handyman' => ['limit' => null],
            'featured_service' => ['limit' => null],
        ];
        $provider_subscription->plan_limitation = json_encode($plan_limitation);

        $provider_subscription->description = 'Basic free plan';
        $provider_subscription->save();

        $user->is_subscribe = 0;
        $user->save();

        $message = __('messages.cancelled_plan', ['plan' => $provider_subscription->title]) .
                   ' You have been moved to the Basic (Free) plan.';
    }

    return comman_message_response($message);
}



    public function getHistory(Request $request){
        $user_id = auth()->id();
        $subscription_history = ProviderSubscription::where('user_id',$user_id);
        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby: 'asc';

        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $subscription_history->count();
            }
        }

        $subscription_history = $subscription_history->orderBy('id',$orderBy)->paginate($per_page);
        $items = ProviderSubscribeResource::collection($subscription_history);

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

    /**
     * Handle bank transfer payment for subscription
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankTransfer(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required',
                'plan_type' => 'required|string',
                'plan_amount' => 'required|numeric',
            ]);

            // Get the new plan details
            $newPlan = Plans::where('title', $request->plan_type)->first();
            if (!$newPlan) {
                return comman_message_response('Plan not found.', 404);
            }

            $user_id = auth()->id();
            $user = User::find($user_id);
            
            if (!$user) {
                return comman_message_response('User not found.', 404);
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
                sendBankTransferConfirmationEmail($user, $existing_subscription, $payment);

                $subscriptionResource = new ProviderSubscribeResource($existing_subscription);
                
                return comman_custom_response([
                    'data' => $subscriptionResource,
                    'message' => 'Subscription upgrade recorded. Please send proof of payment to billing@frobster.com.'
                ]);
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
                    sendBankTransferConfirmationEmail($user, $result, $payment);

                    $subscriptionResource = new ProviderSubscribeResource($result);
                    
                    return comman_custom_response([
                        'data' => $subscriptionResource,
                        'message' => 'Subscription created. Please send proof of payment to billing@frobster.com.'
                    ]);
                } else {
                    return comman_message_response('Failed to create subscription.', 500);
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return comman_message_response($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Bank transfer subscription payment failed: ' . $e->getMessage());
            return comman_message_response('Bank transfer payment failed: ' . $e->getMessage(), 500);
        }
    }
}
