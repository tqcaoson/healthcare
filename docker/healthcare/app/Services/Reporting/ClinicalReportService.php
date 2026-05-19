<?php

namespace App\Services\Reporting;

use App\Domain\Patient\Models\Patient;

class ClinicalReportService
{
    public function patientSummary(
        Patient $patient
    ): array {

        return [
            'patient' => $patient,
            'encounters' => $patient->encounters,
            'conditions' => $patient->conditions,
            'allergies' => $patient->allergies,
            'medications' => [],
        ];
    }
}