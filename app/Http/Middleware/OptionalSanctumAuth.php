<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates Sanctum personal access tokens when present, without requiring auth.
 * Use on public API routes so Bearer tokens still resolve auth()->user() (e.g. UGC blocks on listings).
 * Inactive (deactivated) accounts are never treated as logged in; tokens are revoked.
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->bearerToken()) {
            return $next($request);
        }

        $token = PersonalAccessToken::findToken($request->bearerToken());
        if (! $token || ! $token->tokenable) {
            return $next($request);
        }

        $user = $token->tokenable;
        $status = User::query()->whereKey($user->id)->value('status');

        if ((int) $status !== 1) {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            return $next($request);
        }

        Auth::guard('sanctum')->setUser($user);

        return $next($request);
    }
}
