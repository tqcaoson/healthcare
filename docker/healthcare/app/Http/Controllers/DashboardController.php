<?php

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardService $service
    ) {

        return response()->json(
            $service->metrics()
        );
    }
}