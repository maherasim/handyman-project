<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostJobBid;
use App\Models\PostJobRequest;
use App\Models\User;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProviderBidPlacedMail;
use Stripe\Customer;

class PostJobBidController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function bidshowindex()
    {
        $auth_user = authSession();
        
        // Fetch all bids that belong to the logged-in provider
        $postJobBids = PostJobBid::where('provider_id', $auth_user->id)->get();
    
        $pageTitle = trans('messages.list_form_title', ['form' => trans('messages.postbid')]);
        $asset = ['datatable'];
    
        return view('postrequest.view', compact('pageTitle', 'auth_user', 'asset', 'postJobBids'));
    }

public function apiIndex(Request $request)
{
    $query = PostJobRequest::query();

    // Role-based visibility
    if (!auth()->user()->hasAnyRole(['admin']) && auth()->user()->user_type !== 'provider') {
        $query->where('customer_id', auth()->id());
    }

    // If provider, exclude jobs they’ve already bid on
    if (auth()->user()->user_type === 'provider') {
        $query->whereDoesntHave('postBidList', function ($q) {
            $q->where('provider_id', auth()->id());
        });
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

    return response()->json([
        'success' => true,
        'data' => $query->paginate(10),
    ]);
}





    

    public function bidshow()
{
    $auth_user = authSession();

    // Fetch all bids that belong to the logged-in provider and load provider data
    $postJobBids = PostJobBid::where('provider_id', $auth_user->id)
    ->with(['provider:id,display_name', 'customer:id,display_name', 'postrequest:id,title', ])
    ->get();
 
    return DataTables::of($postJobBids)
        ->addIndexColumn()
        ->addColumn('provider_name', function ($postJobBid) {
            return $postJobBid->provider->display_name ?? 'N/A';
        })
        ->addColumn('customer_name', function ($postJobBid) {
            return $postJobBid->customer->display_name ?? 'N/A';
        })
        ->addColumn('post_title', function ($postJobBid) {
            return $postJobBid->postrequest->title ?? 'N/A';
        })
        ->toJson();
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
 
     public function store(Request $request) 
     {
        
         $request->validate([
             'post_request_id' => 'required|integer|exists:post_job_requests,id',
             'provider_id' => 'nullable|integer',
             'price' => 'required|numeric',
             'why_choose_me' => 'required|string',
             'duration' => 'nullable|string',
             'status' => 'nullable|string',
         ]);
     
         \DB::beginTransaction();
     
         try {
             // Fetch related PostJobRequest
             $customer = PostJobRequest::findOrFail($request->post_request_id);
             $data = $request->all();
             $data['customer_id'] = $customer->customer_id;
             $data['provider_id'] = auth()->user()->id;
     
             // Do not allow update when existing bid is cancelled
             $existingBid = PostJobBid::where('provider_id', $data['provider_id'])
                 ->where('post_request_id', $data['post_request_id'])
                 ->first();
             if ($existingBid && strtolower((string)($existingBid->status ?? '')) === 'cancelled') {
                 $msg = __('messages.not_allowed_to_update_cancelled_bid');
                 if ($request->is('api/*') || $request->wantsJson()) {
                     return response()->json(['message' => $msg], 403);
                 }
                 return redirect()->back()->with('error', $msg);
             }
     
             // Update or create PostJobBid using provider_id + post_request_id as unique keys
             $result = PostJobBid::updateOrCreate([
                 'provider_id'     => $data['provider_id'],
                 'post_request_id' => $data['post_request_id'],
             ], $data);
     
             $activity_data = [
                 'activity_type' => 'provider_send_bid',
                 'bid_data' => $result,
                 'postjob_data' => $customer,
             ];
             // Notify job creator (customer): in-app notification + email when a provider places a bid
             if ($result->wasRecentlyCreated) {
                 $this->sendNotification($activity_data);

                 // Send detailed bid-placed email directly, regardless of DB mail-template enable state
                 try {
                     $customerUser = $customer->customer ?? User::find($customer->customer_id);
                     $providerUser = auth()->user();
                     if ($customerUser && $customerUser->email && $providerUser) {
                         Mail::to($customerUser->email)->locale(getRecipientLocale($customerUser))->send(new ProviderBidPlacedMail($customerUser, $result, $customer, $providerUser, getRecipientLocale($customerUser)));
                     }
                 } catch (\Throwable $e) {
                     \Log::error('Failed to send provider bid placed email: ' . $e->getMessage());
                 }
             }
     
             $message = __('messages.update_form', ['form' => __('messages.postbid')]);
             if ($result->wasRecentlyCreated) {
                 $message = __('messages.save_form', ['form' => __('messages.postbid')]);
             }
     
             \DB::commit();
     
             if ($request->is('api/*')) {
                 return comman_message_response($message);
             }
     
         } catch (\Exception $e) {
             \DB::rollback();
             \Log::error('Failed to save post job bid', ['error' => $e->getMessage()]);
     
             return response()->json(['error' => 'Failed to save post job bid'], 500);
         }
     }
     

    public function PostJobBidData(Request $request){
        $userId = $request->input('user_id');
        $post_request_id = $request->input('post_request_id');
        $postjob = PostJobBid::where('provider_id', $userId)->where('post_request_id',$post_request_id)->first();

        return response()->json($postjob);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
