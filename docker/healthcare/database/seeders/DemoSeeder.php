<?php

namespace Database\Seeders;

use App\Domain\Clinical\Models\Allergy;
use App\Domain\Clinical\Models\Condition;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Medication\Models\MedicationAdministration;
use App\Domain\Order\Models\Order;
use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Models\PatientDemographic;
use App\Domain\Result\Models\LabResult;
use App\Domain\Scheduling\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Department (required FK)
        if (!DB::table('departments')->exists()) {
            DB::table('departments')->insert([
                ['code' => 'NGOAI', 'name' => 'Khoa Ngoại', 'type' => 'outpatient', 'is_active' => true],
                ['code' => 'NOI',   'name' => 'Khoa Nội',   'type' => 'inpatient',  'is_active' => true],
                ['code' => 'ICU',   'name' => 'Khoa ICU',   'type' => 'icu',         'is_active' => true],
                ['code' => 'ED',    'name' => 'Cấp cứu',    'type' => 'ed',          'is_active' => true],
                ['code' => 'LAB',   'name' => 'Xét nghiệm', 'type' => 'lab',         'is_active' => true],
            ]);
        }

        $adminUserId = DB::table('users')->first()?->id ?? 1;

        $patientsData = [
            ['family_name' => 'Nguyễn', 'given_name' => 'Văn An',    'birth_date' => '1975-03-15', 'gender' => 'male',   'phone' => '0901234567', 'email' => 'vanan@email.com'],
            ['family_name' => 'Trần',   'given_name' => 'Thị Bình',  'birth_date' => '1985-07-22', 'gender' => 'female', 'phone' => '0912345678', 'email' => 'tbinh@email.com'],
            ['family_name' => 'Lê',     'given_name' => 'Minh Cường', 'birth_date' => '1968-11-01', 'gender' => 'male',   'phone' => '0923456789', 'email' => null],
            ['family_name' => 'Phạm',   'given_name' => 'Thị Dung',  'birth_date' => '1992-05-18', 'gender' => 'female', 'phone' => '0934567890', 'email' => 'tdung@email.com'],
            ['family_name' => 'Hoàng',  'given_name' => 'Văn Phúc',  'birth_date' => '1950-08-30', 'gender' => 'male',   'phone' => '0945678901', 'email' => null],
            ['family_name' => 'Vũ',     'given_name' => 'Thị Hoa',   'birth_date' => '2000-02-14', 'gender' => 'female', 'phone' => '0956789012', 'email' => 'thoa@email.com'],
        ];

        $patients = [];
        foreach ($patientsData as $idx => $data) {
            $mrn = 'MRN-' . str_pad($idx + 1001, 6, '0', STR_PAD_LEFT);

            if (Patient::where('mrn', $mrn)->exists()) {
                $patients[] = Patient::where('mrn', $mrn)->first();
                continue;
            }

            $patient = Patient::create([
                'uuid'   => Str::uuid(),
                'mrn'    => $mrn,
                'status' => 'active',
            ]);

            PatientDemographic::create([
                'patient_id'  => $patient->id,
                'given_name'  => $data['given_name'],
                'family_name' => $data['family_name'],
                'birth_date'  => $data['birth_date'],
                'gender'      => $data['gender'],
                'phone'       => $data['phone'],
                'email'       => $data['email'],
                'is_current'  => true,
                'changed_by'  => $adminUserId,
                'changed_at'  => now(),
            ]);

            $patients[] = $patient;
        }

        // Allergies for first 3 patients
        $allergyData = [
            [$patients[0]->id, 'medication', 'Penicillin',  'severe'],
            [$patients[1]->id, 'food',        'Hải sản',    'moderate'],
            [$patients[2]->id, 'medication',  'Aspirin',    'mild'],
        ];
        foreach ($allergyData as [$pid, $cat, $substance, $severity]) {
            Allergy::firstOrCreate(
                ['patient_id' => $pid, 'substance_name' => $substance],
                [
                    'category'              => $cat,
                    'clinical_status'       => 'active',
                    'verification'          => 'confirmed',
                    'substance_name'        => $substance,
                    'reaction_severity'     => $severity,
                    'recorded_by'           => $adminUserId,
                    'recorded_at'           => now()->subDays(30),
                ]
            );
        }

        // Conditions
        $conditionData = [
            [$patients[0]->id, 'E11', 'Type 2 diabetes mellitus', 'active'],
            [$patients[2]->id, 'I10', 'Essential hypertension',   'active'],
            [$patients[4]->id, 'J45', 'Asthma',                   'active'],
        ];
        foreach ($conditionData as [$pid, $code, $display, $status]) {
            Condition::firstOrCreate(
                ['patient_id' => $pid, 'icd10_code' => $code],
                [
                    'uuid'            => Str::uuid(),
                    'icd10_code'      => $code,
                    'icd10_display'   => $display,
                    'category'        => 'problem-list-item',
                    'clinical_status' => $status,
                    'verification'    => 'confirmed',
                    'recorded_at'     => now()->subDays(60),
                    'recorded_by'     => $adminUserId,
                ]
            );
        }

        // Encounters
        $encounters = [];
        $encounterData = [
            [$patients[0]->id, 'outpatient', 'AMB',  'in-progress', 'Kiểm tra đường huyết định kỳ', now()->subDays(1),  null],
            [$patients[1]->id, 'inpatient',  'IMP',  'in-progress', 'Nhập viện theo dõi thai kỳ',   now()->subDays(3),  null],
            [$patients[2]->id, 'emergency',  'EMER', 'finished',    'Đau ngực cấp',                 now()->subDays(7),  now()->subDays(6)],
            [$patients[3]->id, 'outpatient', 'AMB',  'finished',    'Khám phụ khoa định kỳ',        now()->subDays(14), now()->subDays(14)],
            [$patients[4]->id, 'inpatient',  'IMP',  'in-progress', 'Viêm phổi cấp tính',           now()->subDays(2),  null],
            [$patients[5]->id, 'outpatient', 'AMB',  'planned',     'Khám tiêu hoá',                now()->addDays(1),  null],
        ];

        foreach ($encounterData as $idx => [$pid, $type, $class, $status, $reason, $start, $end]) {
            $enc = Encounter::create([
                'uuid'                  => Str::uuid(),
                'patient_id'            => $pid,
                'encounter_type'        => $type,
                'status'                => $status,
                'class'                 => $class,
                'reason_text'           => $reason,
                'period_start'          => $start,
                'period_end'            => $end,
                'attending_provider_id' => $adminUserId,
                'department_id'         => ($idx % 4) + 1,
            ]);
            $encounters[] = $enc;
        }

        // Orders
        $orderTypes = ['lab', 'radiology', 'medication', 'lab', 'medication', 'nursing'];
        $priorities  = ['routine', 'stat', 'routine', 'urgent', 'routine', 'routine'];
        $indications = [
            'Kiểm tra HbA1c, glucose',
            'X-quang ngực thẳng — khó thở',
            'Paracetamol 500mg uống',
            'Troponin, CPK-MB khẩn',
            'Salbutamol MDI theo PRN',
            'Theo dõi sinh hiệu mỗi 4 giờ',
        ];
        $statuses = ['active', 'completed', 'completed', 'active', 'active', 'pending'];

        foreach ($encounters as $idx => $enc) {
            Order::create([
                'uuid'                => Str::uuid(),
                'order_number'        => 'ORD-' . strtoupper(Str::random(8)),
                'encounter_id'        => $enc->id,
                'patient_id'          => $enc->patient_id,
                'order_type'          => $orderTypes[$idx] ?? 'lab',
                'status'              => $statuses[$idx] ?? 'pending',
                'priority'            => $priorities[$idx] ?? 'routine',
                'ordered_by'          => $adminUserId,
                'ordered_at'          => $enc->period_start,
                'clinical_indication' => $indications[$idx] ?? null,
            ]);
        }

        // Lab Results
        $labData = [
            [$patients[0]->id, '4548-4', 'Hemoglobin A1c/Hemoglobin.total in Blood', 7.8,  '%',      'H',  7.0,  5.7,   6.5,  false],
            [$patients[0]->id, '2345-7', 'Glucose [Mass/volume] in Blood',           148,  'mg/dL',  'H',  100,  70,    99,   false],
            [$patients[2]->id, '6598-7', 'Troponin I.cardiac [Mass/volume] in Blood',2.4,  'ng/mL',  'HH', 0.04, null,  0.04, true],
            [$patients[2]->id, '2160-0', 'Creatinine [Mass/volume] in Serum',        1.1,  'mg/dL',  'N',  0.95, 0.7,   1.2,  false],
            [$patients[4]->id, '718-7',  'Hemoglobin [Mass/volume] in Blood',        10.2, 'g/dL',   'L',  12.0, 12.0,  17.5, false],
            [$patients[4]->id, '4544-3', 'Hematocrit [Volume Fraction] of Blood',    32,   '%',      'L',  40,   38,    52,   false],
        ];

        foreach ($labData as [$pid, $code, $display, $val, $unit, $flag, $refText, $refLow, $refHigh, $critical]) {
            LabResult::create([
                'uuid'            => Str::uuid(),
                'patient_id'      => $pid,
                'loinc_code'      => $code,
                'loinc_display'   => $display,
                'value_quantity'  => $val,
                'value_unit'      => $unit,
                'abnormal_flag'   => $flag,
                'reference_low'   => $refLow,
                'reference_high'  => $refHigh,
                'reference_text'  => $refLow . ' – ' . $refHigh . ' ' . $unit,
                'status'          => 'final',
                'resulted_at'     => now()->subHours(rand(1, 48)),
                'is_critical'     => $critical,
            ]);
        }

        // Medication Administrations — need real order IDs
        $medData = [
            [$patients[0]->id, $encounters[0]->id, 'Metformin',    '861004', 500,   'mg', 'oral'],
            [$patients[1]->id, $encounters[1]->id, 'Folic Acid',   '4511',   5,     'mg', 'oral'],
            [$patients[2]->id, $encounters[2]->id, 'Aspirin',      '1191',   300,   'mg', 'oral'],
            [$patients[4]->id, $encounters[4]->id, 'Amoxicillin',  '723',    500,   'mg', 'oral'],
            [$patients[4]->id, $encounters[4]->id, 'Dexamethasone','3264',   8,     'mg', 'IV'],
        ];

        foreach ($medData as [$pid, $encId, $drug, $rxnorm, $dose, $unit, $route]) {
            $orderId = Order::where('patient_id', $pid)->value('id');
            if (!$orderId) continue;

            MedicationAdministration::create([
                'uuid'                           => Str::uuid(),
                'order_id'                       => $orderId,
                'patient_id'                     => $pid,
                'encounter_id'                   => $encId,
                'status'                         => 'completed',
                'patient_verified_by_barcode'    => false,
                'medication_verified_by_barcode' => false,
                'drug_name'                      => $drug,
                'rxnorm_code'                    => $rxnorm,
                'dose_value'                     => $dose,
                'dose_unit'                      => $unit,
                'route'                          => $route,
                'administered_at'                => now()->subHours(rand(1, 12)),
                'administered_by'                => $adminUserId,
                'is_prn'                         => false,
            ]);
        }

        // Appointments
        $apptData = [
            [$patients[0]->id, 'followup',  'Tái khám đường huyết',    now()->addDays(7),  now()->addDays(7)->addMinutes(30)],
            [$patients[1]->id, 'routine',   'Khám thai tuần 36',        now()->addDays(3),  now()->addDays(3)->addMinutes(45)],
            [$patients[3]->id, 'checkup',   'Khám tổng quát hàng năm',  now()->addDays(14), now()->addDays(14)->addMinutes(60)],
            [$patients[5]->id, 'routine',   'Khám tiêu hoá',            now()->addDays(1),  now()->addDays(1)->addMinutes(30)],
        ];

        foreach ($apptData as [$pid, $type, $reason, $start, $end]) {
            Appointment::create([
                'uuid'             => Str::uuid(),
                'patient_id'       => $pid,
                'provider_id'      => $adminUserId,
                'department_id'    => 1,
                'status'           => 'booked',
                'appointment_type' => $type,
                'reason'           => $reason,
                'start_at'         => $start,
                'end_at'           => $end,
                'duration_minutes' => (int) $start->diffInMinutes($end),
            ]);
        }

        $this->command->info('Demo data seeded: 6 patients, 6 encounters, 6 orders, 6 lab results, 5 medications, 4 appointments.');
    }
}
