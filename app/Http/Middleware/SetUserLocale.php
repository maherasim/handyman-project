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
        $localeSetByDomain = !empty($domainLocale) && isset($domainLocale[$host]);

        if ($localeSetByDomain) {
            // LanguageTranslator already set locale by domain; do not override
        } elseif (!config('app.show_language_switcher', false)) {
            \App::setLocale(config('app.locale'));
        } elseif (Auth::check() && Auth::user()->language_option) {
            \App::setLocale(Auth::user()->language_option);
        }

        return $next($request);
    }
}
