<?php

namespace App\Domain\Clinical\Services;

use App\Domain\Order\Models\Order;
use Illuminate\Support\Collection;

class CdsAlertService
{
    public function checkOrder(Order $order): Collection
    {
        $alerts = collect();

        if ($order->order_type === 'medication') {
            $alerts = $alerts->merge(
                $this->checkDrugInteractions($order)
            );
        }

        $alerts = $alerts->merge(
            $this->checkAllergyConflicts($order)
        );

        return $alerts->sortByDesc('severity');
    }

    protected function checkDrugInteractions(
        Order $order
    ): Collection {

        return collect();
    }

    protected function checkAllergyConflicts(
        Order $order
    ): Collection {

        return collect();
    }
}