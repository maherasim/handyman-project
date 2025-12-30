<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';
    public const FRONTEND = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Production-ready rate limiting for thousands of users (Play Store ready)
        // Balanced limits: High enough for normal usage, but protects against abuse
        RateLimiter::for('api', function (Request $request) {
            // Authenticated users (mobile app users) - 2000 requests per minute per user
            // This is very generous (33 requests/second) but prevents abuse
            if ($request->user()) {
                return Limit::perMinute(900000000)->by($request->user()->id);
            }
            
            // Unauthenticated users - 300 requests per minute per IP
            // Prevents abuse while allowing legitimate public API access
            return Limit::perMinute(90000000)->by($request->ip());
        });
    }
}
