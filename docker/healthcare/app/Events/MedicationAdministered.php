<?php

namespace App\Events;

use App\Domain\Medication\Models\MedicationAdministration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicationAdministered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MedicationAdministration $administration
    ) {
    }
}
