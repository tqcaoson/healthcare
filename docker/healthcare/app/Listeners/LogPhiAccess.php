<?php

namespace App\Listeners;

use App\Events\PhiAccessed;
use App\Domain\Audit\Services\AuditService;

class LogPhiAccess
{
    public function __construct(
        protected AuditService $auditService
    ) {
    }

    public function handle(PhiAccessed $event): void
    {
        $this->auditService->log('phi.accessed', [
            'resource_type' => class_basename($event->resource),
            'resource_id' => $event->resource->id ?? null,
            'action' => 'view',
        ]);
    }
}