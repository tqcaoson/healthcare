<?php

namespace App\Services\Analytics;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;

class DashboardService
{
    public function metrics(): array
    {
        return [
            'patients_total' => Patient::count(),

            'active_encounters' => Encounter::query()
                ->where(
                    'status',
                    'in-progress'
                )
                ->count(),

            'admissions_today' => Encounter::query()
                ->whereDate(
                    'created_at',
                    today()
                )
                ->count(),
        ];
    }
}