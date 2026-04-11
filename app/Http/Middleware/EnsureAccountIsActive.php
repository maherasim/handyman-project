<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Log out users with inactive accounts (status !== 1) on web and API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ((int) $user->status === 1) {
            return $next($request);
        }

        // Invalidate Sanctum tokens so mobile apps lose access immediately.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => __('messages.deactivate'),
            ], 403);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => __('messages.deactivate')]);
    }
}
