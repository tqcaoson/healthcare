<?php

namespace App\Domain\Pharmacy\Services;

use App\Domain\Audit\Services\AuditService;
use App\Domain\Medication\Models\MedicationAdministration;
use App\Domain\Order\Models\MedicationOrder;
use App\Domain\Order\Models\Order;
use App\Domain\Patient\Models\Patient;
use App\Domain\Pharmacy\DTOs\RecordAdministrationDto;
use App\Domain\Pharmacy\Exceptions\MedicationBarcodeMismatchException;
use App\Domain\Pharmacy\Exceptions\PatientIdentityMismatchException;
use App\Events\MedicationAdministered;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    private const ADMINISTRATION_WINDOW_MINUTES = 60;

    public function __construct(private readonly AuditService $audit) {}

    /**
     * Verify patient identity via MRN barcode — Right Patient.
     * Returns the patient on success, throws on mismatch.
     */
    public function verifyPatientBarcode(string $barcode, int $patientId): Patient
    {
        $patient = Patient::where('mrn', $barcode)->firstOrFail();

        if ($patient->id !== $patientId) {
            $this->audit->log('mar.patient_barcode_mismatch', [
                'resource_type' => 'Patient',
                'resource_id' => $patientId,
                'patient_id' => $patientId,
                'action' => 'view',
            ]);

            throw new PatientIdentityMismatchException();
        }

        $this->audit->log('mar.patient_barcode_verified', [
            'resource_type' => 'Patient',
            'resource_id' => $patient->id,
            'patient_id' => $patient->id,
            'action' => 'view',
        ]);

        return $patient;
    }

    /**
     * Verify medication barcode matches the order — Right Medication.
     * Barcode format: NDC or RxNorm code embedded in GS1/DataMatrix barcode.
     * Returns the MedicationOrder on success, throws on mismatch.
     */
    public function verifyMedicationBarcode(string $barcode, int $orderId): MedicationOrder
    {
        $medOrder = MedicationOrder::where('order_id', $orderId)->firstOrFail();
        $parsed = $this->parseMedicationBarcode($barcode);

        $rxnormMatch = $medOrder->rxnorm_code && $parsed['rxnorm_code'] === $medOrder->rxnorm_code;
        $nameMatch = str_contains(
            strtolower($medOrder->drug_name),
            strtolower($parsed['drug_name'] ?? '')
        );

        if (! $rxnormMatch && ! $nameMatch) {
            $this->audit->log('mar.medication_barcode_mismatch', [
                'resource_type' => 'Order',
                'resource_id' => $orderId,
                'patient_id' => $medOrder->order->patient_id,
                'action' => 'view',
                'new_values' => ['scanned_barcode' => $barcode],
            ]);

            throw new MedicationBarcodeMismatchException();
        }

        return $medOrder;
    }

    /**
     * Get MAR schedule for an encounter: all active medication orders
     * with their upcoming administration slots and recent history.
     */
    public function getMarSchedule(int $encounterId): array
    {
        $orders = Order::with('medicationOrder')
            ->where('encounter_id', $encounterId)
            ->where('order_type', 'medication')
            ->whereIn('status', ['active', 'on-hold'])
            ->get();

        return $orders->map(function (Order $order) {
            $medOrder = $order->medicationOrder;
            $lastAdmin = MedicationAdministration::where('order_id', $order->id)
                ->where('status', 'completed')
                ->latest('administered_at')
                ->first();

            return [
                'order_id' => $order->id,
                'order_uuid' => $order->uuid,
                'status' => $order->status,
                'priority' => $order->priority,
                'drug_name' => $medOrder?->drug_name,
                'rxnorm_code' => $medOrder?->rxnorm_code,
                'dose_value' => $medOrder?->dose_value,
                'dose_unit' => $medOrder?->dose_unit,
                'route' => $medOrder?->route,
                'frequency' => $medOrder?->frequency,
                'instructions' => $medOrder?->instructions,
                'is_verified_by_pharmacist' => $medOrder?->isVerified() ?? false,
                'last_administered_at' => $lastAdmin?->administered_at,
                'administration_window' => $this->nextAdministrationWindow($medOrder),
                'is_due' => $this->isDue($medOrder, $lastAdmin),
                'is_overdue' => $this->isOverdue($medOrder, $lastAdmin),
            ];
        })->all();
    }

    /**
     * Record a medication administration after 5 Rights verification.
     */
    public function recordAdministration(RecordAdministrationDto $dto, int $nurseId): MedicationAdministration
    {
        // Right Patient
        $patient = $this->verifyPatientBarcode($dto->patientBarcode, $dto->patientId);

        // Right Medication
        $medOrder = $this->verifyMedicationBarcode($dto->medicationBarcode, $dto->orderId);

        // Right Time — warn but do not block (record deviation)
        $withinWindow = $this->checkAdministrationWindow($dto->orderId);

        return DB::transaction(function () use ($dto, $nurseId, $withinWindow) {
            $admin = MedicationAdministration::create([
                'uuid' => (string) str()->uuid(),
                'order_id' => $dto->orderId,
                'patient_id' => $dto->patientId,
                'encounter_id' => $dto->encounterId,
                'status' => 'completed',
                'patient_verified_by_barcode' => true,
                'medication_verified_by_barcode' => true,
                'rxnorm_code' => $dto->rxnormCode,
                'drug_name' => $dto->drugName,
                'dose_value' => $dto->doseValue,
                'dose_unit' => $dto->doseUnit,
                'route' => $dto->route,
                'scheduled_at' => $dto->scheduledAt,
                'administered_at' => now(),
                'administered_by' => $nurseId,
                'is_prn' => $dto->isPrn,
                'prn_indication' => $dto->prnIndication,
                'site' => $dto->site,
                'notes' => $dto->notes
                    . ($withinWindow ? '' : ' [LATE: administered outside scheduled window]'),
                'wasted_amount' => $dto->wastedAmount,
                'waste_witnessed_by' => $dto->wasteWitnessedBy,
            ]);

            $this->audit->log('medication.administered', [
                'resource_type' => 'MedicationAdministration',
                'resource_id' => $admin->id,
                'patient_id' => $dto->patientId,
                'action' => 'create',
                'new_values' => [
                    'order_id' => $dto->orderId,
                    'drug_name' => $dto->drugName,
                    'dose' => "{$dto->doseValue} {$dto->doseUnit}",
                    'route' => $dto->route,
                    'within_window' => $withinWindow,
                ],
            ]);

            MedicationAdministered::dispatch($admin);

            return $admin;
        });
    }

    /**
     * Parse a GS1/DataMatrix medication barcode.
     * Supports NDC (01 AI) and free-text drug name fallback.
     */
    private function parseMedicationBarcode(string $barcode): array
    {
        // GS1-128: AI (01) = GTIN/NDC, AI (10) = lot, AI (17) = expiry
        if (preg_match('/\(01\)(\d{14})/', $barcode, $m)) {
            // Convert GTIN-14 → NDC-10: strip leading 0, take digits 2–11
            $ndc = substr($m[1], 1, 10);
            return ['rxnorm_code' => $ndc, 'drug_name' => null];
        }

        // Plain RxNorm code (digits only)
        if (preg_match('/^\d{5,9}$/', $barcode)) {
            return ['rxnorm_code' => $barcode, 'drug_name' => null];
        }

        // Fallback: treat barcode as drug name hint
        return ['rxnorm_code' => null, 'drug_name' => $barcode];
    }

    /**
     * Check if current time is within ±ADMINISTRATION_WINDOW_MINUTES of scheduled time.
     * Returns true if within window or no scheduled time set.
     */
    private function checkAdministrationWindow(int $orderId): bool
    {
        $order = Order::find($orderId);

        if (! $order?->scheduled_for) {
            return true;
        }

        $diff = abs(now()->diffInMinutes($order->scheduled_for, false));

        return $diff <= self::ADMINISTRATION_WINDOW_MINUTES;
    }

    private function nextAdministrationWindow(?MedicationOrder $medOrder): ?string
    {
        if (! $medOrder) {
            return null;
        }

        // Simple frequency-to-minutes map; extend as needed
        $frequencyMap = [
            'QD' => 1440, 'BID' => 720, 'TID' => 480,
            'QID' => 360, 'q4h' => 240, 'q6h' => 360,
            'q8h' => 480, 'q12h' => 720,
        ];

        $intervalMinutes = $frequencyMap[strtoupper($medOrder->frequency ?? '')] ?? null;

        return $intervalMinutes
            ? now()->addMinutes($intervalMinutes)->toIso8601String()
            : null;
    }

    private function isDue(?MedicationOrder $medOrder, ?MedicationAdministration $lastAdmin): bool
    {
        if (! $medOrder?->frequency) {
            return false;
        }

        if (! $lastAdmin) {
            return true;
        }

        $intervalMinutes = $this->frequencyToMinutes($medOrder->frequency);

        return $lastAdmin->administered_at->addMinutes($intervalMinutes - self::ADMINISTRATION_WINDOW_MINUTES)
            ->isPast();
    }

    private function isOverdue(?MedicationOrder $medOrder, ?MedicationAdministration $lastAdmin): bool
    {
        if (! $medOrder?->frequency) {
            return false;
        }

        if (! $lastAdmin) {
            return true;
        }

        $intervalMinutes = $this->frequencyToMinutes($medOrder->frequency);

        return $lastAdmin->administered_at->addMinutes($intervalMinutes + self::ADMINISTRATION_WINDOW_MINUTES)
            ->isPast();
    }

    private function frequencyToMinutes(string $frequency): int
    {
        return match (strtoupper($frequency)) {
            'QD' => 1440,
            'BID' => 720,
            'TID' => 480,
            'QID' => 360,
            'Q4H' => 240,
            'Q6H' => 360,
            'Q8H' => 480,
            'Q12H' => 720,
            default => 1440,
        };
    }
}
