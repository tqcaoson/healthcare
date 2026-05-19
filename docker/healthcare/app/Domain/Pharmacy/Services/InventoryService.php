<?php

namespace App\Domain\Pharmacy\Services;

use App\Domain\Pharmacy\Models\MedicationInventory;

class InventoryService
{
    public function decrement(
        string $rxnorm,
        int $qty
    ): void {

        MedicationInventory::query()
            ->where('rxnorm_code', $rxnorm)
            ->decrement(
                'quantity_on_hand',
                $qty
            );
    }

    public function lowStock(): array
    {
        return MedicationInventory::query()
            ->whereColumn(
                'quantity_on_hand',
                '<=',
                'reorder_level'
            )
            ->get()
            ->all();
    }
}