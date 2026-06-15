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
        // Match persotel.de and www.persotel.de (same for other mapped domains)
        $hostVariants = array_unique(array_filter([
            $host,
            preg_replace('/^www\./i', '', $host),
            (str_starts_with(strtolower($host), 'www.') ? null : 'www.'.$host),
        ]));

        $localeFromDomain = null;
        foreach ($hostVariants as $h) {
            if (!empty($domainLocale) && isset($domainLocale[$h])) {
                $localeFromDomain = $domainLocale[$h];
                break;
            }
        }

        if ($localeFromDomain !== null) {
            \App::setLocale($localeFromDomain);
        } else {
            $acceptLang = substr($request->header('Accept-Language', ''), 0, 2);
            $supported  = array_keys(config('app.domain_locale', []));
            $supported  = array_unique(array_values(config('app.domain_locale', [])));
            if ($acceptLang && in_array($acceptLang, array_merge(['en', 'de'], $supported))) {
                \App::setLocale($acceptLang);
            } elseif (!config('app.show_language_switcher', false)) {
                \App::setLocale(config('app.locale'));
            } elseif (session()->has('locale')) {
                \App::setLocale(session()->get('locale'));
            }
        }
        $response = $next($request);

        return $response;
    }
}
