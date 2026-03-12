<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class LanguageTranslator
{
    public function handle(Request $request, Closure $next)
    {
        $domainLocale = config('app.domain_locale', []);
        $host = $request->getHost();

        if (!empty($domainLocale) && isset($domainLocale[$host])) {
            \App::setLocale($domainLocale[$host]);
        } elseif (!config('app.show_language_switcher', false)) {
            \App::setLocale(config('app.locale'));
        } elseif (session()->has('locale')) {
            \App::setLocale(session()->get('locale'));
        }
        $response = $next($request);

        return $response;
    }
}
