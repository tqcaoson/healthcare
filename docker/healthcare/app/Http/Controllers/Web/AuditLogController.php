<?php

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAuditLog', \App\Models\User::class);

        $query = AuditLog::query()->orderByDesc('event_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%$search%")
                  ->orWhere('resource_type', 'like', "%$search%")
                  ->orWhere('action', 'like', "%$search%")
                  ->orWhere('ip_address', 'like', "%$search%");
            });
        }

        if ($eventType = $request->input('event_type')) {
            $query->where('event_type', $eventType);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('event_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('event_at', '<=', $dateTo);
        }

        $logs = $query->paginate(50)->withQueryString();

        $eventTypes = AuditLog::query()
            ->selectRaw('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        return view('admin.audit-logs.index', compact('logs', 'eventTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('viewAuditLog', \App\Models\User::class);

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
