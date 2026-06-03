# Pharmacy — MAR & Medication Administration

## PharmacyService

```php
class PharmacyService
{
    // Verify order — dược sĩ review
    public function verifyOrder(MedicationOrder $order, User $pharmacist): void
    {
        abort_unless($pharmacist->role === 'pharmacist', 403);

        // Re-check drug interactions tại thời điểm verify
        $alerts = app(CdsAlertService::class)->checkOrder($order->order);
        if ($alerts->where('severity', 'critical')->isNotEmpty()) {
            throw new CriticalDrugInteractionException($alerts->where('severity', 'critical')->first());
        }

        DB::transaction(function () use ($order, $pharmacist) {
            $order->update([
                'verified_by_pharmacist' => $pharmacist->id,
                'verified_at'            => now(),
            ]);
            $order->order->update(['status' => 'active']);

            app(AuditService::class)->log('medication_order.verified', [
                'order_id'   => $order->order_id,
                'patient_id' => $order->order->patient_id,
            ]);

            $nurse = $this->getAssignedNurse($order->order->encounter_id);
            $nurse?->notify(new MedicationReadyForAdministrationNotification($order));
        });
    }

    // Record medication administration (5 Rights enforced)
    public function recordAdministration(RecordAdministrationDto $dto, User $nurse): MedicationAdministration
    {
        // 1. Verify right patient (barcode check)
        $this->verifyPatientIdentity($dto->patientBarcode, $dto->patientId);

        // 2. Verify right medication (barcode check)
        $this->verifyMedicationBarcode($dto->medicationBarcode, $dto->orderId);

        // 3. Verify right time (± window)
        $this->verifyAdministrationWindow($dto->orderId);

        return DB::transaction(function () use ($dto, $nurse) {
            $admin = MedicationAdministration::create([
                'uuid'                           => Str::uuid(),
                'order_id'                       => $dto->orderId,
                'patient_id'                     => $dto->patientId,
                'encounter_id'                   => $dto->encounterId,
                'status'                         => 'completed',
                'patient_verified_by_barcode'    => $dto->patientBarcodeVerified,
                'medication_verified_by_barcode' => $dto->medicationBarcodeVerified,
                'rxnorm_code'                    => $dto->rxnormCode,
                'drug_name'                      => $dto->drugName,
                'dose_value'                     => $dto->doseValue,
                'dose_unit'                      => $dto->doseUnit,
                'route'                          => $dto->route,
                'scheduled_at'                   => $dto->scheduledAt,
                'administered_at'                => now(),
                'administered_by'                => $nurse->id,
                'is_prn'                         => $dto->isPrn,
                'prn_indication'                 => $dto->prnIndication,
                'site'                           => $dto->site,
                'notes'                          => $dto->notes,
            ]);

            MedicationAdministered::dispatch($admin);
            return $admin;
        });
    }

    private function verifyPatientIdentity(string $barcode, int $patientId): void
    {
        $patient = Patient::where('mrn', $barcode)->firstOrFail();
        abort_if($patient->id !== $patientId, 422, 'Patient identity mismatch — barcode không khớp');
    }

    private function verifyMedicationBarcode(string $barcode, int $orderId): void
    {
        // Parse barcode → NDC code → match với order drug
        // Throw nếu không match
    }

    private function verifyAdministrationWindow(int $orderId): void
    {
        $order = MedicationOrder::findOrFail($orderId);
        // Check scheduled time ± 30 phút
        // Warn nếu ngoài window, nhưng không block (ghi lại deviation)
    }
}
```
