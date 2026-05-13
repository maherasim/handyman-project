<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RecaptchaValidator
{
    public static function validate(Request $request): void
    {
        if (! (bool) config('services.recaptcha.enabled', false)) {
            return;
        }

        $siteKey = (string) config('services.recaptcha.site_key', '');
        $secret = (string) config('services.recaptcha.secret_key', '');

        if ($siteKey === '' || $secret === '') {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => __('auth.recaptcha_not_configured'),
            ]);
        }

        if ((bool) config('services.recaptcha.skip_on_local', true)) {
            $host = (string) $request->getHost();
            if (in_array($host, ['localhost', '127.0.0.1'], true)) {
                return;
            }
        }

        try {
            $resp = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => (string) $request->input('g-recaptcha-response'),
                    'remoteip' => (string) $request->ip(),
                ]);

            $data = $resp->json() ?? [];

            if (! $resp->ok() || ! (bool) ($data['success'] ?? false)) {
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
}
