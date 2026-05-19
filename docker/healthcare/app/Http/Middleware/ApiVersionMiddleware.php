<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersionMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        $version = $request->header(
            'X-API-Version',
            'v1'
        );

        app()->instance(
            'api.version',
            $version
        );

        return $next($request);
    }
}