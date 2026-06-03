<?php

namespace App\Http\Controllers\Web;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;
use App\Domain\Order\Models\Order;
use App\Domain\Result\Models\LabResult;
use App\Domain\Scheduling\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $stats = [
            'patients_total'      => Patient::active()->count(),
            'patients_new_month'  => Patient::where('created_at', '>=', now()->startOfMonth())->count(),
            'encounters_active'   => Encounter::where('status', 'in-progress')->count(),
            'encounters_today'    => Encounter::whereDate('period_start', today())->count(),
            'orders_pending'      => Order::where('status', 'pending')->count(),
            'orders_stat'         => Order::where('priority', 'stat')->whereNotIn('status', ['cancelled','completed'])->count(),
            'results_critical'    => LabResult::where('is_critical', true)->whereNull('acknowledged_at')->count(),
            'results_today'       => LabResult::whereDate('resulted_at', today())->count(),
            'appointments_today'  => Appointment::whereDate('start_at', today())->count(),
            'appointments_booked' => Appointment::where('status', 'booked')
                ->where('start_at', '>=', now())->count(),
        ];

        // Encounters per day (last 7 days)
        $encountersTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date'  => $date,
                'count' => Encounter::whereDate('period_start', $date)->count(),
            ];
        });

        // Order types breakdown
        $ordersByType = Order::selectRaw('order_type, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('order_type')
            ->pluck('count', 'order_type');

        return view('reports.index', compact('stats', 'encountersTrend', 'ordersByType'));
    }
}
