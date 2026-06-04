<?php

namespace Tests\Feature\MAR;

use App\Domain\Medication\Models\MedicationAdministration;
use App\Domain\Order\Models\MedicationOrder;
use App\Domain\Order\Models\Order;
use App\Domain\Patient\Models\Patient;
use App\Events\MedicationAdministered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MARScanTest extends TestCase
{
    use RefreshDatabase;

    private User $nurse;
    private Patient $patient;
    private Order $order;
    private MedicationOrder $medOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nurse = User::create([
            'uuid' => (string) str()->uuid(),
            'username' => 'nurse_test',
            'email' => 'nurse@test.com',
            'password' => bcrypt('password'),
            'role' => 'nurse',
            'mfa_enabled' => false,
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'uuid' => (string) str()->uuid(),
            'mrn' => 'MRN-TEST-001',
            'status' => 'active',
        ]);

        $encounter = \App\Domain\Encounter\Models\Encounter::create([
            'uuid' => (string) str()->uuid(),
            'patient_id' => $this->patient->id,
            'encounter_type' => 'inpatient',
            'status' => 'in-progress',
            'class' => 'IMP',
            'period_start' => now(),
            'department_id' => \DB::table('departments')->insertGetId([
                'code' => 'WARD-A',
                'name' => 'Ward A',
                'type' => 'inpatient',
                'is_active' => true,
            ]),
        ]);

        $this->order = Order::create([
            'uuid' => (string) str()->uuid(),
            'order_number' => 'ORD-' . strtoupper(str()->random(8)),
            'encounter_id' => $encounter->id,
            'patient_id' => $this->patient->id,
            'order_type' => 'medication',
            'status' => 'active',
            'priority' => 'routine',
            'ordered_by' => $this->nurse->id,
            'ordered_at' => now(),
        ]);

        $this->medOrder = MedicationOrder::create([
            'order_id' => $this->order->id,
            'rxnorm_code' => '861004',
            'drug_name' => 'Metformin 500mg',
            'dose_value' => 500,
            'dose_unit' => 'mg',
            'route' => 'oral',
            'frequency' => 'BID',
            'verified_by_pharmacist' => $this->nurse->id,
            'verified_at' => now(),
        ]);
    }

    // ----------------------------------------------------------------
    // MAR Schedule
    // ----------------------------------------------------------------

    public function test_nurse_can_fetch_mar_schedule(): void
    {
        $this->actingAs($this->nurse)
            ->getJson("/api/v1/mar/schedule?encounter_id={$this->order->encounter_id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'order_id',
                        'drug_name',
                        'dose_value',
                        'dose_unit',
                        'route',
                        'frequency',
                        'is_verified_by_pharmacist',
                        'is_due',
                        'is_overdue',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.drug_name', 'Metformin 500mg')
            ->assertJsonPath('data.0.is_verified_by_pharmacist', true);
    }

    public function test_mar_schedule_requires_encounter_id(): void
    {
        $this->actingAs($this->nurse)
            ->getJson('/api/v1/mar/schedule')
            ->assertUnprocessable();
    }

    // ----------------------------------------------------------------
    // Patient Barcode Scan
    // ----------------------------------------------------------------

    public function test_correct_patient_barcode_is_verified(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/scan/patient', [
                'barcode' => 'MRN-TEST-001',
                'patient_id' => $this->patient->id,
            ])
            ->assertOk()
            ->assertJsonPath('verified', true)
            ->assertJsonPath('patient.mrn', 'MRN-TEST-001');
    }

    public function test_wrong_patient_barcode_returns_422(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/scan/patient', [
                'barcode' => 'WRONG-MRN',
                'patient_id' => $this->patient->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('verified', false)
            ->assertJsonPath('message', 'Patient identity mismatch — barcode không khớp');
    }

    public function test_patient_barcode_scan_is_audited(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/scan/patient', [
                'barcode' => 'MRN-TEST-001',
                'patient_id' => $this->patient->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'mar.patient_barcode_verified',
            'user_id' => $this->nurse->id,
        ]);
    }

    // ----------------------------------------------------------------
    // Medication Barcode Scan
    // ----------------------------------------------------------------

    public function test_matching_rxnorm_barcode_is_verified(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/scan/medication', [
                'barcode' => '861004',   // RxNorm code
                'order_id' => $this->order->id,
            ])
            ->assertOk()
            ->assertJsonPath('verified', true)
            ->assertJsonPath('medication.drug_name', 'Metformin 500mg');
    }

    public function test_wrong_medication_barcode_returns_422(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/scan/medication', [
                'barcode' => '999999',   // different RxNorm
                'order_id' => $this->order->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('verified', false)
            ->assertJsonPath('message', 'Medication barcode không khớp với order — kiểm tra lại thuốc');
    }

    // ----------------------------------------------------------------
    // Full Administration Flow
    // ----------------------------------------------------------------

    public function test_nurse_can_record_administration_after_scan(): void
    {
        Event::fake([MedicationAdministered::class]);

        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/administer', [
                'order_id' => $this->order->id,
                'patient_id' => $this->patient->id,
                'encounter_id' => $this->order->encounter_id,
                'patient_barcode' => 'MRN-TEST-001',
                'medication_barcode' => '861004',
                'drug_name' => 'Metformin 500mg',
                'rxnorm_code' => '861004',
                'dose_value' => 500,
                'dose_unit' => 'mg',
                'route' => 'oral',
            ])
            ->assertCreated()
            ->assertJsonPath('data.patient_verified_by_barcode', true)
            ->assertJsonPath('data.medication_verified_by_barcode', true)
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('medication_administrations', [
            'order_id' => $this->order->id,
            'patient_id' => $this->patient->id,
            'patient_verified_by_barcode' => 1,
            'medication_verified_by_barcode' => 1,
            'status' => 'completed',
        ]);

        Event::assertDispatched(MedicationAdministered::class);
    }

    public function test_administration_fails_on_wrong_patient_barcode(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/administer', [
                'order_id' => $this->order->id,
                'patient_id' => $this->patient->id,
                'encounter_id' => $this->order->encounter_id,
                'patient_barcode' => 'WRONG-MRN',
                'medication_barcode' => '861004',
                'drug_name' => 'Metformin 500mg',
                'dose_value' => 500,
                'dose_unit' => 'mg',
                'route' => 'oral',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Patient identity mismatch — barcode không khớp');

        $this->assertDatabaseMissing('medication_administrations', [
            'order_id' => $this->order->id,
        ]);
    }

    public function test_administration_is_audited(): void
    {
        Event::fake([MedicationAdministered::class]);

        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/administer', [
                'order_id' => $this->order->id,
                'patient_id' => $this->patient->id,
                'encounter_id' => $this->order->encounter_id,
                'patient_barcode' => 'MRN-TEST-001',
                'medication_barcode' => '861004',
                'drug_name' => 'Metformin 500mg',
                'dose_value' => 500,
                'dose_unit' => 'mg',
                'route' => 'oral',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'medication.administered',
            'user_id' => $this->nurse->id,
            'patient_id' => $this->patient->id,
        ]);
    }

    public function test_pharmacist_cannot_administer_medication(): void
    {
        $pharmacist = User::create([
            'uuid' => (string) str()->uuid(),
            'username' => 'pharm_test',
            'email' => 'pharm@test.com',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
            'mfa_enabled' => false,
            'status' => 'active',
        ]);

        $this->actingAs($pharmacist) // pharmacist không có quyền ghi nhận phát thuốc
            ->postJson('/api/v1/mar/administer', [
                'order_id' => $this->order->id,
                'patient_id' => $this->patient->id,
                'encounter_id' => $this->order->encounter_id,
                'patient_barcode' => 'MRN-TEST-001',
                'medication_barcode' => '861004',
                'drug_name' => 'Metformin 500mg',
                'dose_value' => 500,
                'dose_unit' => 'mg',
                'route' => 'oral',
            ])
            ->assertForbidden();
    }

    public function test_prn_administration_requires_indication(): void
    {
        $this->actingAs($this->nurse)
            ->postJson('/api/v1/mar/administer', [
                'order_id' => $this->order->id,
                'patient_id' => $this->patient->id,
                'encounter_id' => $this->order->encounter_id,
                'patient_barcode' => 'MRN-TEST-001',
                'medication_barcode' => '861004',
                'drug_name' => 'Metformin 500mg',
                'dose_value' => 500,
                'dose_unit' => 'mg',
                'route' => 'oral',
                'is_prn' => true,
                // prn_indication missing
            ])
            ->assertUnprocessable();
    }
}
