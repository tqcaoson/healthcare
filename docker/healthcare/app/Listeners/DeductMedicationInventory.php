<?php

namespace App\Listeners;

use App\Domain\Pharmacy\Services\InventoryService;
use App\Events\MedicationAdministered;

class DeductMedicationInventory
{
    public function __construct(
        protected InventoryService $inventory
    ) {
    }

    public function handle(
        MedicationAdministered $event
    ): void {

        $this->inventory->decrement(
            $event->administration->rxnorm_code,
            1
        );
    }
}