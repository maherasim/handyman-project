<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Block inactive accounts on web and API. Re-read status from DB so admin
     * deactivation takes effect on the very next request (Sanctum + session).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user()
            ?? Auth::guard('web')->user();

        if (! $user) {
            return $next($request);
        }

        $status = User::query()->whereKey($user->id)->value('status');

        if ((int) $status === 1) {
            return $next($request);
        }

        // Revoke all tokens for this user id (fresh model not required for relationship).
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $isApiStyle = $request->bearerToken()
            || str_starts_with($request->path(), 'api/')
            || $request->expectsJson()
            || str_contains((string) $request->header('Accept', ''), 'application/json');

        if ($isApiStyle) {
            return response()->json([
                'message' => __('messages.deactivate'),
            ], 403);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->withErrors(['email' => __('messages.deactivate')]);
    }
}
