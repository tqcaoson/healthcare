# Testing

Framework: **Pest PHP**. Dùng `RefreshDatabase` cho feature tests.

## Patient Tests

```php
// tests/Feature/Patient/PatientControllerTest.php
class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_physician_can_view_assigned_patient(): void
    {
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()->withDemographics()->create();
        $encounter = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->getJson("/api/v1/patients/{$patient->uuid}")
            ->assertOk()
            ->assertJsonPath('data.id', $patient->uuid)
            ->assertJsonStructure(['data' => ['id', 'mrn', 'demographics']]);
    }

    public function test_unauthorized_user_cannot_view_patient(): void
    {
        $otherPhysician = User::factory()->physician()->create();
        $patient = Patient::factory()->withDemographics()->create();

        $this->actingAs($otherPhysician)
            ->getJson("/api/v1/patients/{$patient->uuid}")
            ->assertForbidden();  // 403, không phải 404 — không reveal existence
    }

    public function test_phi_access_is_audited(): void
    {
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()->withDemographics()->create();
        Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)->getJson("/api/v1/patients/{$patient->uuid}")->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type'    => 'phi.accessed',
            'user_id'       => $physician->id,
            'resource_type' => 'Patient',
        ]);
    }
}
```

## CPOE Tests

```php
// tests/Feature/Order/CpoeTest.php
class CpoeTest extends TestCase
{
    use RefreshDatabase;

    public function test_medication_order_triggers_pharmacy_notification(): void
    {
        Notification::fake();
        Queue::fake();

        $physician  = User::factory()->physician()->create();
        $pharmacist = User::factory()->pharmacist()->create();
        $patient    = Patient::factory()->withDemographics()->create();
        $encounter  = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->postJson("/api/v1/encounters/{$encounter->uuid}/orders", [
                'order_type'  => 'medication',
                'priority'    => 'routine',
                'drug_name'   => 'Metformin',
                'rxnorm_code' => '861004',
                'dose_value'  => 500,
                'dose_unit'   => 'mg',
                'route'       => 'oral',
                'frequency'   => 'BID',
            ])
            ->assertCreated();

        Queue::assertPushedOn('clinical', SendOutgoingHl7Message::class);
        Notification::assertSentTo($pharmacist, NewMedicationOrderNotification::class);
    }

    public function test_cds_alert_does_not_block_order(): void
    {
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()->withDemographics()->withAllergy('Penicillin')->create();
        $encounter = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->postJson("/api/v1/encounters/{$encounter->uuid}/orders", [
                'order_type'  => 'medication',
                'drug_name'   => 'Amoxicillin',  // penicillin-class
                'rxnorm_code' => '723',
                'dose_value'  => 500,
                'dose_unit'   => 'mg',
                'route'       => 'oral',
                'frequency'   => 'TID',
            ])
            ->assertCreated()  // Tạo được dù có cảnh báo
            ->assertJsonPath('data.cds_alerts.0.severity', 'warning');
    }

    public function test_audit_log_captures_order_cancellation(): void
    {
        $physician = User::factory()->physician()->create();
        $order     = Order::factory()
            ->for(Encounter::factory()->create(['attending_provider_id' => $physician->id]))
            ->active()
            ->create();

        $this->actingAs($physician)
            ->postJson("/api/v1/orders/{$order->uuid}/cancel", ['reason' => 'Patient refused medication'])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', ['event_type' => 'order.cancelled', 'user_id' => $physician->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertModelExists($order->fresh()); // KHÔNG bị xóa khỏi DB
    }
}
```

## Medication Administration Tests

```php
// tests/Feature/Medication/AdministrationTest.php
class MedicationAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_requires_barcode_verification(): void
    {
        $nurse   = User::factory()->nurse()->create();
        $patient = Patient::factory()->withDemographics()->create();
        $order   = MedicationOrder::factory()->verified()->create(['patient_id' => $patient->id]);

        $this->actingAs($nurse)
            ->postJson("/api/v1/encounters/{$order->encounter_id}/medication-administrations", [
                'order_id'           => $order->order_id,
                'patient_barcode'    => 'WRONG-MRN',  // sai
                'medication_barcode' => $order->medication_barcode,
                'administered_at'    => now()->toIso8601String(),
                'dose_value'         => $order->dose_value,
                'dose_unit'          => $order->dose_unit,
                'route'              => $order->route,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Patient identity mismatch — barcode không khớp');
    }
}
```

## Audit Service Tests

```php
// tests/Unit/Services/AuditServiceTest.php
class AuditServiceTest extends TestCase
{
    public function test_audit_log_hash_chain_is_consistent(): void
    {
        $service = app(AuditService::class);

        $service->log('phi.accessed', ['resource_type' => 'Patient', 'resource_id' => 1]);
        $service->log('phi.accessed', ['resource_type' => 'Patient', 'resource_id' => 2]);

        $logs = AuditLog::orderBy('id')->get();

        $this->assertCount(2, $logs);
        $this->assertNotEquals($logs[0]->log_hash, $logs[1]->log_hash);
    }

    public function test_phi_is_scrubbed_from_log_messages(): void
    {
        $scrubber = new ScrubPhiFromLogs();
        $dirty    = 'Processing patient John Doe DOB 1980-01-01 MRN: 123456 phone 555-1234';
        $clean    = $scrubber->scrub($dirty);

        $this->assertStringNotContainsString('John Doe', $clean);
        $this->assertStringNotContainsString('123456', $clean);
        $this->assertStringNotContainsString('555-1234', $clean);
    }
}
```
