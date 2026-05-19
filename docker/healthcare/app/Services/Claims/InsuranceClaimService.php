<?php

namespace App\Services\Claims;

use App\Domain\Billing\Models\Invoice;

class InsuranceClaimService
{
    public function submit(
        Invoice $invoice
    ): array {

        return [
            'claim_id' => fake()->uuid(),
            'status' => 'submitted',
            'submitted_at' => now(),
        ];
    }
}