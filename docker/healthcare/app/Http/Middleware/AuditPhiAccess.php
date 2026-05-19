<?php

namespace App\Http\Middleware;

use App\Domain\Audit\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditPhiAccess
{
    public function __construct(
        protected AuditService $auditService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $response = $next($request);

        if (
            $response->isSuccessful()
            && $this->isPhiEndpoint($request)
        ) {
            $this->auditService->log('phi.accessed', [
                'resource_type' => $this->extractResourceType($request),
                'resource_id' => $request->route('patient')?->id,
                'action' => 'view',
            ]);
        }

        return $response;
    }

    protected function isPhiEndpoint(Request $request): bool
    {
        return str_contains(
            $request->path(),
            'patients'
        );
    }

    protected function extractResourceType(Request $request): string
    {
        return ucfirst(
            explode('/', $request->path())[2] ?? 'Unknown'
        );
    }
}