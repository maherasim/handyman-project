<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
 public function store(Request $request)
{
    $request->validate([
        'username'     => 'required|string|max:255|unique:users',
        'first_name'   => 'required|string|max:255',
        'last_name'    => 'required|string|max:255',
        'email'        => 'required|string|email|max:255|unique:users',
        'password'     => 'required|string|confirmed|min:8',
    ]);

    $userType = $request->usertype ?? 'user';
    $designation = $request->designation ?? null;
    $email = $request->email;
    $username = $request->username;

    $user = User::withTrashed()
        ->where(function ($query) use ($email, $username) {
            $query->where('email', $email)->orWhere('username', $username);
        })
        ->first();

    if ($user) {
        $message = trans('messages.login_form');
        return redirect()->back()->withErrors(['message' => $message]);
    }

    // Select placeholders (e.g. "Select Employer") must not be written to integer FK columns.
    $nullableIntId = static function ($value): ?int {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    };

    $user = User::create([
        'username'         => $username,
        'first_name'       => $request->first_name,
        'last_name'        => $request->last_name,
        'contact_number'   => $request->phone_number,
        'user_type'        => $userType,
        'display_name'     => $request->first_name . " " . $request->last_name,
        'email'            => $email,
        'password'         => Hash::make($request->password),
        'designation'      => $designation,
        'usertype'         => $userType,
        'provider_id'      => $nullableIntId($request->input('provider_id')),
        'providertype_id'  => $nullableIntId($request->input('providertype_id')),
        'handymantype_id'  => $nullableIntId($request->input('handymantype_id')),
        'status'           => $request->status ?? 0,
    ]);

    // Assign role and send verification email
    $user->assignRole($userType);
    $verificationLink = route('verify', ['id' => $user->id]);
    Mail::to($user->email)->send(new VerificationEmail($verificationLink));

    // Create wallet for all user types (provider, handyman, and user)
    if (in_array($userType, ['provider', 'handyman', 'user'])) {
        Wallet::create([
            'title' => $user->display_name,
            'user_id' => $user->id,
            'amount' => 0,
            'status' => 1
        ]);
    }
    
if ($userType === 'provider') {
    $startDate = now();
    $planType = 'weekly'; // You can set this dynamically if needed

    // Calculate end date based on plan type
    switch ($planType) {
        case 'weekly':
            $endDate = $startDate->copy()->addWeek();
            break;
        case 'monthly':
            $endDate = $startDate->copy()->addMonth();
            break;
        case 'yearly':
            $endDate = $startDate->copy()->addYear();
            break;
        default:
            $endDate = $startDate->copy()->addWeek(); // fallback
    }

    \App\Models\ProviderSubscription::create([
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

 
    // Redirect based on user type
    if ($userType === 'user') {
        return redirect(RouteServiceProvider::FRONTEND)->with('status', 'Email Verification link sent to your email. Please verify before logging in.');
    } else {
        return redirect(route('login'))->with('status', 'Email Verification link sent to your email. Please verify before logging in.');
    }
}
}
