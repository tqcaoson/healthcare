# Artisan Commands

## HL7 Commands

```php
// hl7:process {--queue=clinical}
class ProcessHl7MessageCommand extends Command
{
    public function handle(Hl7Parser $parser): void
    {
        Hl7Inbox::where('status', 'received')
            ->orderBy('created_at')
            ->chunk(50, function ($messages) use ($parser) {
                foreach ($messages as $msg) {
                    try {
                        $msg->update(['status' => 'processing']);
                        ProcessIncomingHl7Message::dispatch($msg);
                        $msg->update(['status' => 'processed', 'processed_at' => now()]);
                    } catch (\Throwable $e) {
                        $msg->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                        $this->error("Failed: {$msg->message_control_id} — {$e->getMessage()}");
                    }
                }
            });
    }
}

// hl7:retry-dead {--limit=50}
Hl7Outbox::where('status', 'dead')
    ->limit($this->option('limit'))
    ->get()
    ->each(function ($msg) {
        $msg->update(['status' => 'pending', 'attempts' => 0]);
        SendOutgoingHl7Message::dispatch($msg);
    });
```

## Quality Metrics

```php
// quality:generate {--date=}
class GenerateQualityReportCommand extends Command
{
    public function handle(QualityMetricsService $svc): void
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now()->subDay();
        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $metrics = [
            'date'                    => $date->toDateString(),
            'bed_occupancy'           => $svc->bedOccupancy($date),
            'alos_days'               => $svc->alos($from, $to),
            'ed_arrivals'             => $svc->edArrivals($from, $to),
            'admissions'              => $svc->admissions($from, $to),
            'discharges'              => $svc->discharges($from, $to),
            'lab_stat_tat_min'        => $svc->labTurnaround('STAT', $from, $to)['p50'],
            'critical_labs'           => $svc->criticalLabCount($from, $to),
            'unacknowledged_critical' => $svc->unacknowledgedCriticalCount($from, $to),
        ];

        QualityMetricSnapshot::create($metrics);
    }
}
```

## Session & Audit Commands

```php
// sessions:purge
$count = UserSession::where('last_activity', '<', now()->subMinutes(15))->delete();

// audit:archive {--before=}
AuditLog::where('event_at', '<', $before)
    ->chunk(10000, function ($logs, $page) use ($before) {
        $filename = "audit-archive/{$before->format('Y-m')}/batch-{$page}.jsonl";
        Storage::disk('cold')->put($filename, $logs->map->toJson()->implode("\n"));
    });
```

## Downtime Cache

```php
// downtime:warm-cache
Encounter::where('status', 'in-progress')
    ->with(['patient.currentDemographics', 'patient.allergies', 'patient.conditions'])
    ->chunk(100, function ($encounters) {
        foreach ($encounters as $enc) {
            $snapshot = [
                'mrn'        => $enc->patient->mrn,
                'name'       => $enc->patient->currentDemographics?->family_name,
                'dob'        => $enc->patient->currentDemographics?->birth_date,
                'allergies'  => $enc->patient->allergies->map->only(['substance_name', 'reaction_severity']),
                'conditions' => $enc->patient->conditions->map->only(['icd10_code', 'icd10_display']),
                'department' => $enc->department_id,
                'bed'        => $enc->bed_id,
            ];
            Cache::put("downtime.patient.{$enc->patient->mrn}", $snapshot, now()->addHours(2));
            Cache::put("downtime.patient.{$enc->patient->mrn}.cached_at", now()->toIso8601String(), now()->addHours(2));
        }
    });
```
