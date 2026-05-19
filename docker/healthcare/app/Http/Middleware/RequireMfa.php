<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if (! $user?->mfa_enabled) {
            abort(403, 'MFA required.');
        }

        return $next($request);
    }
}