# Downtime Procedures — Khi hệ thống gặp sự cố

## Health Check Endpoint `/health`

```php
class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database'   => $this->checkDatabase(),
            'redis'      => $this->checkRedis(),
            'queue'      => $this->checkQueue(),
            'lis'        => $this->checkHl7Connection('LIS'),
            'ris'        => $this->checkHl7Connection('RIS'),
            'pharmacy'   => $this->checkHl7Connection('PHARMACY'),
            'disk_space' => $this->checkDiskSpace(),
        ];

        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'ok');

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'degraded',
            'checks'    => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'DB unreachable'];
        }
    }

    private function checkQueue(): array
    {
        $pending = Hl7Outbox::where('status', 'pending')->count();
        $dead    = Hl7Outbox::where('status', 'dead')->count();
        return ['status' => $pending > 500 ? 'warning' : 'ok', 'pending_hl7' => $pending, 'dead_hl7' => $dead];
    }

    private function checkDiskSpace(): array
    {
        $freeBytes = disk_free_space('/');
        $freePct   = round($freeBytes / disk_total_space('/') * 100, 1);
        return ['status' => $freePct < 10 ? 'warning' : 'ok', 'free_pct' => $freePct];
    }
}
```

## Downtime Read-Only Mode

```php
class DowntimeController extends Controller
{
    // Không cần full auth nhưng cần downtime token
    // Trả về cached snapshot — clinicians vẫn xem được khi hệ thống down
    public function patientSnapshot(string $mrn): JsonResponse
    {
        $snapshot = Cache::get("downtime.patient.{$mrn}");
        abort_if(! $snapshot, 404, 'Patient snapshot không có trong downtime cache');

        return response()->json([
            'mode'     => 'downtime',
            'warning'  => 'Dữ liệu có thể không cập nhật. Dùng cho tham khảo khẩn cấp.',
            'snapshot' => $snapshot,
            'cached_at' => Cache::get("downtime.patient.{$mrn}.cached_at"),
        ]);
    }
}
```

Cache được pre-warm bằng lệnh `downtime:warm-cache` (xem [skill/commands.md](../skill/commands.md)), chạy mỗi giờ theo schedule.
