<?php

namespace App\Domain\Encounter\Services;

use App\Domain\Encounter\Models\Encounter;
use App\Events\PatientAdmitted;
use App\Events\PatientDischarged;

class AdtService
{
    public function admit(array $payload): Encounter
    {
        $encounter = Encounter::create([
            'uuid' => (string) str()->uuid(),
            'patient_id' => $payload['patient_id'],
            'encounter_type' => 'inpatient',
            'status' => 'in-progress',
            'class' => 'IMP',
            'period_start' => now(),
            'department_id' => $payload['department_id'],
            'bed_id' => $payload['bed_id'] ?? null,
        ]);

        PatientAdmitted::dispatch($encounter);

        return $encounter;
    }

    public function discharge(
        Encounter $encounter,
        string $disposition
    ): void {

        $encounter->update([
            'status' => 'finished',
            'discharge_disposition' => $disposition,
            'period_end' => now(),
        ]);

        PatientDischarged::dispatch($encounter);
    }
}