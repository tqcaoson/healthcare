<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOutgoingHl7Message;
use App\Domain\Audit\Services\AuditService;

class RouteOrderToSubsystem
{
    public function __construct(
        protected AuditService $auditService
    ) {
    }

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        SendOutgoingHl7Message::dispatch($order);

        $this->auditService->log('order.routed', [
            'resource_type' => 'Order',
            'resource_id' => $order->id,
            'patient_id' => $order->patient_id,
            'action' => 'route',
        ]);
    }
}
