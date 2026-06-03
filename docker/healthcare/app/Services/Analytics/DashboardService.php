<?php

namespace App\Services\Analytics;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;
use App\Domain\Scheduling\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function metrics(): array
    {
        return [
            'patients_total'    => Patient::count(),
            'active_encounters' => Encounter::query()->where('status', 'in-progress')->count(),
            'admissions_today'  => Encounter::query()->whereDate('created_at', today())->count(),
        ];
    }

    public function patientCard(): array
    {
        $patient = Patient::with([
            'currentDemographics',
            'conditions',
            'allergies',
            'latestVitals',
        ])->whereHas('encounters', fn ($q) => $q->where('status', 'in-progress'))
          ->latest()
          ->first();

        return [
            'patient'    => $patient,
            'demo'       => $patient?->currentDemographics,
            'conditions' => $patient?->conditions ?? collect(),
            'vitals'     => $patient?->latestVitals,
        ];
    }

    public function weekAppointments(?int $patientId): Collection
    {
        $start = Carbon::now()->startOfWeek();
        $end   = Carbon::now()->endOfWeek();

        $query = Appointment::query()
            ->whereBetween('start_at', [$start, $end])
            ->where('status', '!=', 'cancelled');

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        return $query->get();
    }

    public function doctors(): array
    {
        $providerIds = Appointment::query()
            ->whereBetween('start_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->where('status', '!=', 'cancelled')
            ->distinct()
            ->pluck('provider_id')
            ->take(4);

        $users = \App\Models\User::whereIn('id', $providerIds)->get()->keyBy('id');

        return $providerIds->map(fn ($id) => [
            'id'        => $id,
            'name'      => $users->get($id)?->name ?? 'Provider #' . $id,
            'specialty' => 'Physician',
        ])->values()->toArray();
    }

    public function calendarMonth(): array
    {
        $today = Carbon::today();

        $busyDays = Appointment::query()
            ->whereYear('start_at', $today->year)
            ->whereMonth('start_at', $today->month)
            ->where('status', '!=', 'cancelled')
            ->selectRaw(
                config('database.default') === 'sqlite'
                    ? "CAST(strftime('%d', start_at) AS INTEGER) as d"
                    : 'DAY(start_at) as d'
            )
            ->distinct()
            ->pluck('d')
            ->toArray();

        return [
            'month'     => $today->format('F Y'),
            'today'     => $today->day,
            'first_dow' => $today->copy()->startOfMonth()->dayOfWeek,
            'days_in'   => $today->daysInMonth,
            'busy_days' => $busyDays,
        ];
    }
}
