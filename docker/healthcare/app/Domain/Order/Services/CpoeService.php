<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Models\Order;
use App\Events\OrderPlaced;
use Illuminate\Support\Facades\DB;

class CpoeService
{
    public function placeOrder(
        array $payload,
        $provider
    ): Order {

        return DB::transaction(function () use (
            $payload,
            $provider
        ) {

            $order = Order::create([
                'uuid' => (string) str()->uuid(),
                'order_number' => fake()->unique()->numerify('ORD#####'),
                'encounter_id' => $payload['encounter_id'],
                'patient_id' => $payload['patient_id'],
                'order_type' => $payload['order_type'],
                'status' => 'pending',
                'priority' => $payload['priority'] ?? 'routine',
                'ordered_by' => $provider->id,
                'ordered_at' => now(),
                'clinical_indication' => $payload['clinical_indication'] ?? null,
            ]);

            OrderPlaced::dispatch($order);

            return $order;
        });
    }

    public function cancelOrder(
        Order $order,
        string $reason,
        $by
    ): void {

        if ($order->status === 'completed') {
            abort(422, 'Completed order cannot be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $by->id,
            'cancel_reason' => $reason,
        ]);
    }
}