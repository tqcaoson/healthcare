<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Domain\Clinical\Services\CdsAlertService;

class TriggerCdsAlert
{
    public function __construct(
        protected CdsAlertService $cdsAlertService
    ) {
    }

    public function handle(OrderPlaced $event): void
    {
        $this->cdsAlertService->checkOrder($event->order);
    }
}
