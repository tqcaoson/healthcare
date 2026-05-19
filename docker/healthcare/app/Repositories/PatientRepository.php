<?php

namespace App\Repositories;

use App\Domain\Patient\Models\Patient;

class PatientRepository
{
    public function findByMrn(
        string $mrn
    ): ?Patient {

        return Patient::query()
            ->where('mrn', $mrn)
            ->first();
    }

    public function search(
        array $filters = []
    ) {

        return Patient::query()
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) =>
                    $query->where(
                        'status',
                        $status
                    )
            );
    }
}