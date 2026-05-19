<?php

namespace App\Http\Middleware;

use App\Domain\Audit\Services\AuditService;
use Closure;
use Illuminate\Http\Request;

class BreakGlassAccess
{
    public function __construct(
        protected AuditService $audit
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ) {

        if (
            $request->header('X-Break-Glass')
        ) {

            $this->audit->log(
                'security.break_glass',
                [
                    'reason' => $request->header(
                        'X-Break-Glass-Reason'
                    ),
                ]
            );
        }

        return $next($request);
    }
}