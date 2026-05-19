<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function generate(
        int $patientId,
        int $encounterId,
        array $items
    ): Invoice {

        return DB::transaction(function () use (
            $patientId,
            $encounterId,
            $items
        ) {

            $subtotal = collect($items)
                ->sum(fn ($item) =>
                    $item['quantity'] * $item['unit_price']
                );

            $invoice = Invoice::create([
                'uuid' => (string) str()->uuid(),
                'patient_id' => $patientId,
                'encounter_id' => $encounterId,
                'invoice_number' => fake()->unique()
                    ->numerify('INV#####'),
                'status' => 'draft',
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'tax' => 0,
                'discount' => 0,
                'total' => $subtotal,
                'issued_at' => now(),
            ]);

            foreach ($items as $item) {

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    ...$item,
                    'line_total' =>
                        $item['quantity'] *
                        $item['unit_price'],
                ]);
            }

            return $invoice;
        });
    }
}