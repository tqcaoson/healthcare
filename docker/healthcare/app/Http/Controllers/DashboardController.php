<?php

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardService $service
    ) {

        return view('dashboard.index', [
            'metrics' => $service->metrics(),
        ]);
    }
}