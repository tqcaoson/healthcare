<?php

namespace App\Domain\Pharmacy\DTOs;

use Carbon\Carbon;

final class RecordAdministrationDto
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $patientId,
        public readonly int $encounterId,
        public readonly string $patientBarcode,
        public readonly string $medicationBarcode,
        public readonly bool $patientBarcodeVerified,
        public readonly bool $medicationBarcodeVerified,
        public readonly string $drugName,
        public readonly float $doseValue,
        public readonly string $doseUnit,
        public readonly string $route,
        public readonly ?string $rxnormCode,
        public readonly ?Carbon $scheduledAt,
        public readonly bool $isPrn = false,
        public readonly ?string $prnIndication = null,
        public readonly ?string $site = null,
        public readonly ?string $notes = null,
        public readonly ?float $wastedAmount = null,
        public readonly ?int $wasteWitnessedBy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            patientId: $data['patient_id'],
            encounterId: $data['encounter_id'],
            patientBarcode: $data['patient_barcode'],
            medicationBarcode: $data['medication_barcode'],
            patientBarcodeVerified: false,
            medicationBarcodeVerified: false,
            drugName: $data['drug_name'],
            doseValue: $data['dose_value'],
            doseUnit: $data['dose_unit'],
            route: $data['route'],
            rxnormCode: $data['rxnorm_code'] ?? null,
            scheduledAt: isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            isPrn: $data['is_prn'] ?? false,
            prnIndication: $data['prn_indication'] ?? null,
            site: $data['site'] ?? null,
            notes: $data['notes'] ?? null,
            wastedAmount: $data['wasted_amount'] ?? null,
            wasteWitnessedBy: $data['waste_witnessed_by'] ?? null,
        );
    }
}
