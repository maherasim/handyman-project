<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $domainLocale = config('app.domain_locale', []);
        $host = $request->getHost();
        $hostVariants = array_unique([$host, preg_replace('/^www\./i', '', $host)]);
        if (! str_starts_with(strtolower($host), 'www.')) {
            $hostVariants[] = 'www.'.$host;
        }
        $localeSetByDomain = false;
        foreach ($hostVariants as $h) {
            if (! empty($domainLocale) && isset($domainLocale[$h])) {
                $localeSetByDomain = true;
                break;
            }
        }

        if ($localeSetByDomain) {
            // Domain locale is the default, but the authenticated user's saved
            // language preference always overrides it (explicit choice > domain default).
            if (Auth::check() && Auth::user()->language_option) {
                \App::setLocale(Auth::user()->language_option);
            }
            // If no user preference, LanguageTranslator already set domain or session locale.
        } elseif (!config('app.show_language_switcher', false)) {
            \App::setLocale(config('app.locale'));
        } elseif (Auth::check() && Auth::user()->language_option) {
            \App::setLocale(Auth::user()->language_option);
        }

        return $next($request);
    }
}
