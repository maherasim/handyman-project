<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share session cookie between www and non-www so API auth works from both (e.g. /api/postbid/rating-by-provider/save)
        if (config('session.domain') === null && env('APP_URL')) {
            $host = parse_url(env('APP_URL'), PHP_URL_HOST);
            if ($host && !in_array($host, ['localhost', '127.0.0.1'], true)) {
                $root = (strpos($host, 'www.') === 0) ? '.' . substr($host, 4) : '.' . $host;
                config(['session.domain' => $root]);
            }
        }
    }
}
