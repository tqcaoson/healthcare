<?php

namespace App\Http\Middleware;

use App\Services\Observability\CorrelationIdService;
use Closure;
use Illuminate\Http\Request;

class InjectCorrelationId
{
    public function __construct(
        protected CorrelationIdService $service
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ) {

        $response = $next($request);

        $response->headers->set(
            'X-Correlation-ID',
            $this->service->current()
        );

        return $response;
    }
}