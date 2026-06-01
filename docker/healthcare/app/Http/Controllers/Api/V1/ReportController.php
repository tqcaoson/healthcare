<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    public function quality(Request $request): JsonResponse
    {
        $from = $request->date('from', 'Y-m-d', now()->startOfMonth()->toDateString());
        $to = $request->date('to', 'Y-m-d', now()->toDateString());

        return response()->json([
            'data' => [
                'period' => ['from' => $from, 'to' => $to],
            ],
        ]);
    }
}
