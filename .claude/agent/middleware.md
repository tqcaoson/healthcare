# Middleware

## AuditPhiAccess

```php
class AuditPhiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log PHI access sau khi response thành công
        if ($response->isSuccessful() && $this->isPhiEndpoint($request)) {
            app(AuditService::class)->log('phi.accessed', [
                'resource_type' => $this->extractResourceType($request),
                'resource_id'   => $this->extractResourceId($request),
                'patient_id'    => $this->extractPatientId($request, $response),
                'action'        => 'view',
            ]);
        }

        return $response;
    }
}
```

## ScrubPhiFromLogs

```php
class ScrubPhiFromLogs
{
    // Chạy trước tất cả requests — đảm bảo Laravel logs không chứa PHI
    private array $patterns = [
        'mrn'   => '/\bMRN[-:\s]*\d{6,12}\b/i',
        'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
        'email' => '/\b[\w.]+@[\w.]+\.\w+\b/',
        'dob'   => '/\b(DOB|date_of_birth|birth_date)[":\s]+\d{4}-\d{2}-\d{2}\b/i',
        'ssn'   => '/\b\d{3}-\d{2}-\d{4}\b/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Register custom log formatter với PHI scrubbing
        // Áp dụng cho toàn bộ log output trong request này
        return $next($request);
    }
}
```

## AutoLogout

```php
class AutoLogout
{
    private int $idleMinutes = 15; // HIPAA: auto-logout sau idle

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_at');

            if ($lastActivity && now()->diffInMinutes($lastActivity) > $this->idleMinutes) {
                Auth::logout();
                session()->invalidate();

                app(AuditService::class)->log('session.auto_logout', [
                    'reason'       => 'idle_timeout',
                    'idle_minutes' => $this->idleMinutes,
                ]);

                return response()->json(['message' => 'Session expired'], 401);
            }

            session(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}
```
