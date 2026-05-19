<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [

        \App\Http\Middleware\InjectCorrelationId::class,

        \App\Http\Middleware\ScrubPhiFromLogs::class,
    ];

    protected $middlewareGroups = [

        'api' => [

            'throttle:api',

            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,

            \App\Http\Middleware\ApiVersionMiddleware::class,

            \App\Http\Middleware\AuditPhiAccess::class,
        ],
    ];

    protected $routeMiddleware = [

        'break-glass' =>
            \App\Http\Middleware\BreakGlassAccess::class,

        'require-mfa' =>
            \App\Http\Middleware\RequireMfa::class,
    ];
}