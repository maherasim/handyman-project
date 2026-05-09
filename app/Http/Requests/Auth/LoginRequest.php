<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $recaptchaEnabled = (bool) config('services.recaptcha.enabled', false);

        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'g-recaptcha-response' => $recaptchaEnabled ? 'required|string' : 'nullable|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $this->validateRecaptchaIfEnabled();

        if (! Auth::attempt($this->only('email', 'password'), $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        $user = Auth::user();
        if($user->status == 0) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('auth.account_inactive')
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function validateRecaptchaIfEnabled(): void
    {
        if (! (bool) config('services.recaptcha.enabled', false)) {
            return;
        }

        $siteKey = (string) config('services.recaptcha.site_key', '');
        $secret = (string) config('services.recaptcha.secret_key', '');

        // Misconfigured: fail closed to avoid bypassing captcha unintentionally.
        if ($siteKey === '' || $secret === '') {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => __('auth.recaptcha_not_configured'),
            ]);
        }

        if ((bool) config('services.recaptcha.skip_on_local', true)) {
            $host = (string) $this->getHost();
            if (in_array($host, ['localhost', '127.0.0.1'], true)) {
                return;
            }
        }

        $token = (string) $this->input('g-recaptcha-response');
        $remoteIp = (string) $this->ip();

        try {
            $resp = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            $data = $resp->json() ?? [];
            $ok = (bool) ($data['success'] ?? false);

            if (! $resp->ok() || ! $ok) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => __('auth.recaptcha_failed'),
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => __('auth.recaptcha_failed'),
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
