<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates Sanctum personal access tokens when present, without requiring auth.
 * Use on public API routes so Bearer tokens still resolve auth()->user() (e.g. UGC blocks on listings).
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if ($token && $token->tokenable) {
                Auth::guard('sanctum')->setUser($token->tokenable);
            }
        }

        return $next($request);
    }
}
