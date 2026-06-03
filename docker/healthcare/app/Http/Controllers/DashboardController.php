<?php

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $service)
    {
        $card    = $service->patientCard();
        $patient = $card['patient'];

        return view('dashboard.index', [
            'metrics'      => $service->metrics(),
            'patient'      => $patient,
            'demo'         => $card['demo'],
            'conditions'   => $card['conditions'],
            'vitals'       => $card['vitals'],
            'appointments' => $service->weekAppointments($patient?->id),
            'doctors'      => $service->doctors(),
            'calendar'     => $service->calendarMonth(),
        ]);
    }
}
