<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Pharmacy\DTOs\RecordAdministrationDto;
use App\Domain\Pharmacy\Exceptions\MedicationBarcodeMismatchException;
use App\Domain\Pharmacy\Exceptions\PatientIdentityMismatchException;
use App\Domain\Pharmacy\Services\PharmacyService;
use App\Http\Controllers\Controller;
use App\Http\Requests\MAR\RecordAdministrationRequest;
use App\Http\Requests\MAR\ScanMedicationRequest;
use App\Http\Requests\MAR\ScanPatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MARScanController extends Controller
{
    public function __construct(private readonly PharmacyService $pharmacy) {}

    /**
     * GET /api/v1/mar/schedule
     * Returns all active medication orders for an encounter with due/overdue flags.
     */
    public function schedule(Request $request): JsonResponse
    {
        $request->validate([
            'encounter_id' => ['required', 'integer', 'exists:encounters,id'],
        ]);

        $schedule = $this->pharmacy->getMarSchedule($request->integer('encounter_id'));

        return response()->json(['data' => $schedule]);
    }

    /**
     * POST /api/v1/mar/scan/patient
     * Right Patient: verify patient wristband barcode.
     */
    public function scanPatient(ScanPatientRequest $request): JsonResponse
    {
        try {
            $patient = $this->pharmacy->verifyPatientBarcode(
                $request->string('barcode'),
                $request->integer('patient_id'),
            );

            return response()->json([
                'verified' => true,
                'patient' => [
                    'id' => $patient->id,
                    'mrn' => $patient->mrn,
                    'uuid' => $patient->uuid,
                ],
            ]);
        } catch (PatientIdentityMismatchException $e) {
            return response()->json([
                'verified' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/mar/scan/medication
     * Right Medication: verify medication barcode (NDC/RxNorm) against order.
     */
    public function scanMedication(ScanMedicationRequest $request): JsonResponse
    {
        try {
            $medOrder = $this->pharmacy->verifyMedicationBarcode(
                $request->string('barcode'),
                $request->integer('order_id'),
            );

            return response()->json([
                'verified' => true,
                'medication' => [
                    'order_id' => $medOrder->order_id,
                    'drug_name' => $medOrder->drug_name,
                    'rxnorm_code' => $medOrder->rxnorm_code,
                    'dose_value' => $medOrder->dose_value,
                    'dose_unit' => $medOrder->dose_unit,
                    'route' => $medOrder->route,
                    'frequency' => $medOrder->frequency,
                    'instructions' => $medOrder->instructions,
                ],
            ]);
        } catch (MedicationBarcodeMismatchException $e) {
            return response()->json([
                'verified' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/mar/administer
     * Record medication administration after barcode scan (5 Rights enforced).
     */
    public function administer(RecordAdministrationRequest $request): JsonResponse
    {
        try {
            $dto = RecordAdministrationDto::fromArray($request->validated());
            $administration = $this->pharmacy->recordAdministration($dto, $request->user()->id);

            return response()->json([
                'data' => [
                    'uuid' => $administration->uuid,
                    'order_id' => $administration->order_id,
                    'patient_id' => $administration->patient_id,
                    'drug_name' => $administration->drug_name,
                    'dose_value' => $administration->dose_value,
                    'dose_unit' => $administration->dose_unit,
                    'route' => $administration->route,
                    'administered_at' => $administration->administered_at,
                    'patient_verified_by_barcode' => $administration->patient_verified_by_barcode,
                    'medication_verified_by_barcode' => $administration->medication_verified_by_barcode,
                    'status' => $administration->status,
                ],
            ], 201);
        } catch (PatientIdentityMismatchException|MedicationBarcodeMismatchException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
