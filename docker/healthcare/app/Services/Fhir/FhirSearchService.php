<?php

namespace App\Services\Fhir;

use App\Domain\Patient\Models\Patient;

class FhirSearchService
{
    public function patientSearch(
        array $filters
    ) {

        return Patient::query()
            ->when(
                $filters['identifier'] ?? null,
                fn ($query, $identifier) =>
                    $query->where(
                        'mrn',
                        $identifier
                    )
            )
            ->paginate();
    }
}