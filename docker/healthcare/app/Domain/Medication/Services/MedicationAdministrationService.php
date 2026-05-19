<?php

namespace App\Domain\Medication\Services;

use App\Domain\Medication\Models\MedicationAdministration;
use App\Events\MedicationAdministered;

class MedicationAdministrationService
{
    public function administer(array $payload)
    {
        $admin = MedicationAdministration::create([
            'uuid' => (string) str()->uuid(),
            ...$payload,
        ]);

        MedicationAdministered::dispatch($admin);

        return $admin;
    }
}