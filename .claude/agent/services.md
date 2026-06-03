# Services — Business Logic

## MpiService (Master Patient Index)

```php
class MpiService
{
    // Tìm patient — tránh duplicate trước khi tạo mới
    public function findOrMatch(array $demographics): ?Patient
    {
        // 1. Exact match bằng identifier (BHYT, CCCD)
        // 2. Probabilistic match: tên + ngày sinh + giới tính
        // 3. Return null nếu không match (tạo mới)
    }

    // Merge 2 duplicate records
    public function mergePatients(Patient $primary, Patient $secondary): void
    {
        // Chuyển tất cả encounters, orders, results về primary
        // Mark secondary là inactive
        // Log merge event vào audit
        // KHÔNG xóa secondary record
    }
}
```

## CpoeService (Order Entry)

```php
class CpoeService
{
    public function placeOrder(PlaceOrderDto $dto, User $provider): Order
    {
        return DB::transaction(function () use ($dto, $provider) {
            // 1. Validate order (diagnosis required for lab orders, etc.)
            $this->validateOrder($dto);

            // 2. Tạo Order record
            $order = Order::create([...]);

            // 3. Tạo type-specific record (LabOrder/MedicationOrder/etc.)
            $this->createTypeSpecificOrder($order, $dto);

            // 4. Persist vào outbox TRONG CÙNG TRANSACTION (outbox pattern)
            $this->queueHl7Message($order);

            // 5. Fire event → CDS check async (không block)
            OrderPlaced::dispatch($order);

            return $order;
        });
    }

    // Cancel order — KHÔNG delete, chỉ cancel với lý do
    public function cancelOrder(Order $order, string $reason, User $by): void
    {
        // Validate: không cancel nếu đã completed
        // Update status + log
        // Gửi cancel message đến downstream (LIS/Pharmacy)
    }
}
```

## CdsAlertService (Clinical Decision Support)

```php
class CdsAlertService
{
    // Rule: không bao giờ block order — chỉ cảnh báo
    public function checkOrder(Order $order): Collection
    {
        $alerts = collect();

        if ($order->order_type === 'medication') {
            $alerts = $alerts->merge($this->checkDrugInteractions($order));
        }

        $alerts = $alerts->merge($this->checkAllergyConflicts($order));
        $alerts = $alerts->merge($this->checkDoseRange($order));

        return $alerts->sortByDesc('severity');
    }

    // Alert fatigue prevention: track override rates
    public function recordOverride(CdsAlert $alert, string $reason, User $provider): void
    {
        CdsAlertOverride::create([
            'alert_type'    => $alert->type,
            'overridden_by' => $provider->id,
            'reason'        => $reason,
            'overridden_at' => now(),
        ]);
    }
}
```

## AuditService

```php
class AuditService
{
    public function log(string $eventType, array $context = []): void
    {
        $entry = array_merge([
            'event_type'    => $eventType,
            'event_at'      => now(),
            'user_id'       => Auth::id(),
            'user_name'     => Auth::user()?->username,
            'user_role'     => Auth::user()?->role,
            'session_id'    => session()->getId(),
            'ip_address'    => request()->ip(),
            'request_path'  => request()->path(),
            'request_method'=> request()->method(),
            'correlation_id'=> request()->header('X-Correlation-ID', (string) Str::uuid()),
        ], $context);

        $entry['log_hash'] = $this->computeHash($entry);

        // Insert — KHÔNG update, KHÔNG delete
        AuditLog::insert($entry);
    }

    private function computeHash(array $entry): string
    {
        $lastHash = AuditLog::latest('id')->value('log_hash') ?? '';
        return hash('sha256', $lastHash . json_encode($entry));
    }
}
```

## QualityMetricsService

```php
class QualityMetricsService
{
    // 30-day readmission rate
    public function readmissionRate(Carbon $from, Carbon $to, ?int $departmentId = null): float
    {
        // Đếm patients discharge trong period
        // Cross-reference với admissions trong 30 ngày sau discharge
    }

    // Average Length of Stay
    public function alos(Carbon $from, Carbon $to): float
    {
        return Encounter::whereBetween('period_start', [$from, $to])
            ->whereNotNull('period_end')
            ->where('encounter_type', 'inpatient')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, period_start, period_end) / 24'));
    }

    // Lab Turnaround Time (order → result)
    public function labTurnaround(string $loincCode, Carbon $from, Carbon $to): array
    {
        // Min, median, p75, p90, max TAT
    }

    // Bed occupancy rate
    public function bedOccupancy(Carbon $date, ?int $departmentId = null): float
    {
        $total    = Bed::when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $occupied = Bed::where('status', 'occupied')->when($departmentId, ...)->count();
        return $total > 0 ? round($occupied / $total * 100, 1) : 0;
    }

    // Medication error rate
    public function medicationErrorRate(Carbon $from, Carbon $to): float
    {
        // Errors logged / Total administrations × 1000
    }
}
```

## TelehealthService

```php
class TelehealthService
{
    public function createSession(Appointment $appointment): TelehealthSession
    {
        $session = TelehealthSession::create([
            'uuid'           => Str::uuid(),
            'appointment_id' => $appointment->id,
            'patient_id'     => $appointment->patient_id,
            'provider_id'    => $appointment->provider_id,
            'room_id'        => Str::uuid()->toString(),
            'scheduled_at'   => $appointment->start_at,
        ]);

        $this->generatePatientToken($session);
        return $session;
    }

    private function generatePatientToken(TelehealthSession $session): void
    {
        $payload = [
            'sub'     => 'patient:' . $session->patient_id,
            'room'    => $session->room_id,
            'session' => $session->uuid,
            'iat'     => now()->timestamp,
            'exp'     => $session->scheduled_at->addHours(2)->timestamp,
            'perms'   => ['video:publish', 'video:subscribe'],
        ];

        $token = JWT::encode($payload, config('telehealth.secret'), 'HS256');

        $session->update([
            'patient_token'            => $token,
            'patient_token_expires_at' => $session->scheduled_at->addHours(2),
        ]);
    }

    public function startSession(TelehealthSession $session, User $provider): Encounter
    {
        $encounter = Encounter::create([
            'uuid'                  => Str::uuid(),
            'patient_id'            => $session->patient_id,
            'encounter_type'        => 'telehealth',
            'status'                => 'in-progress',
            'class'                 => 'VR',
            'period_start'          => now(),
            'attending_provider_id' => $provider->id,
            'department_id'         => $provider->department_id,
        ]);

        $session->update(['status' => 'in-progress', 'provider_joined_at' => now(), 'encounter_id' => $encounter->id]);
        return $encounter;
    }

    public function endSession(TelehealthSession $session): void
    {
        $duration = $session->provider_joined_at ? now()->diffInSeconds($session->provider_joined_at) : 0;

        $session->update(['status' => 'completed', 'ended_at' => now(), 'duration_seconds' => $duration]);

        if ($session->encounter_id) {
            Encounter::find($session->encounter_id)->update(['status' => 'finished', 'period_end' => now()]);
        }
    }
}
```
