# Jobs & Queues

Queue priorities: `critical > clinical > default > analytics`

## Core Jobs

```php
class NotifyCriticalLabValue implements ShouldQueue
{
    public string $queue = 'critical';
    public int $tries    = 5;
    public int $timeout  = 30;

    public function handle(): void
    {
        // 1. Notify ordering provider qua in-app notification
        // 2. Ghi vào audit log
        // 3. Require acknowledgment trong 30 phút
        // 4. Nếu chưa acknowledge → escalate đến charge nurse
    }
}

class ProcessIncomingHl7Message implements ShouldQueue
{
    public string $queue = 'clinical';

    public function handle(): void
    {
        // Idempotency check: đã process message_control_id này chưa?
        if (Hl7Inbox::where('message_control_id', $this->messageControlId)->exists()) {
            return; // đã xử lý, bỏ qua
        }

        // Parse HL7 → domain objects → persist
        // Send ACK về sender
    }
}

class SendOutgoingHl7Message implements ShouldQueue
{
    public string $queue = 'clinical';
    public int $tries    = 3;

    public function handle(): void
    {
        // Dùng outbox record (đã ghi trong transaction với business event)
        // Gửi qua MLLP → nhận ACK
        // Mark outbox record là 'sent'
        // Nếu NACK hoặc timeout → mark 'failed' → retry với backoff
    }

    public function failed(\Throwable $e): void
    {
        // Sau max retries → mark 'dead' → alert on-call
        $this->outbox->update(['status' => 'dead']);
    }
}
```

## Critical Value Escalation (Scheduled Job)

```php
class CheckUnacknowledgedCriticalValues implements ShouldQueue
{
    public function handle(): void
    {
        LabResult::where('is_critical', true)
            ->whereNull('acknowledged_at')
            ->where('resulted_at', '<', now()->subMinutes(30))
            ->with(['patient', 'order.orderedBy'])
            ->chunk(100, function ($results) {
                foreach ($results as $result) {
                    $chargeNurse = $this->getChargeNurse($result->encounter->department_id);
                    $chargeNurse?->notify(new CriticalValueEscalationNotification($result));

                    app(AuditService::class)->log('critical_value.escalated', [
                        'lab_result_id'          => $result->id,
                        'patient_id'             => $result->patient_id,
                        'minutes_unacknowledged' => $result->resulted_at->diffInMinutes(now()),
                    ]);
                }
            });
    }
}
```

## Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Critical value escalation — check every 5 minutes
    $schedule->job(new CheckUnacknowledgedCriticalValues)
        ->everyFiveMinutes()
        ->name('check-critical-values')
        ->withoutOverlapping()
        ->onFailure(fn() => Log::error('Critical value check failed'));

    // Appointment reminders
    $schedule->command('appointment:send-reminders --hours=24')->dailyAt('08:00');
    $schedule->command('appointment:send-reminders --hours=1')->hourly();

    // Quality metrics snapshot
    $schedule->command('quality:generate')->dailyAt('01:00');

    // Retry dead HL7 messages
    $schedule->command('hl7:retry-dead')->everyThirtyMinutes();

    // Session cleanup
    $schedule->command('sessions:purge')->everyFifteenMinutes();

    // Audit log archive (hàng tháng)
    $schedule->command('audit:archive --before=' . now()->subYears(2)->toDateString())->monthly();

    // Password expiry reminder
    $schedule->command('users:notify-password-expiry')->dailyAt('07:00');
}
```
