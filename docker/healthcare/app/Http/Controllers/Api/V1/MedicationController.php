<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Medication\Models\MedicationAdministration;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $administrations = MedicationAdministration::query()
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('encounter_id'), fn ($q) => $q->where('encounter_id', $request->encounter_id))
            ->orderByDesc('administered_at')
            ->paginate(20);

        return response()->json(['data' => $administrations]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'required|exists:encounters,id',
            'drug_name' => 'required|string',
            'dose_value' => 'required|numeric',
            'dose_unit' => 'required|string',
            'route' => 'required|string',
        ]);

        $administration = MedicationAdministration::create([
            'uuid' => (string) str()->uuid(),
            'status' => 'completed',
            'administered_at' => now(),
            'administered_by' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json(['data' => $administration], 201);
    }

    public function show(MedicationAdministration $medication): JsonResponse
    {
        return response()->json(['data' => $medication]);
    }

    public function update(Request $request, MedicationAdministration $medication): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:in-progress,completed,not-done,on-hold,stopped,unknown',
            'notes' => 'sometimes|nullable|string',
        ]);

        $medication->update($validated);

        return response()->json(['data' => $medication->fresh()]);
    }

    public function destroy(MedicationAdministration $medication): JsonResponse
    {
        $medication->update(['status' => 'not-done']);

        return response()->json(['message' => 'Administration voided.']);
    }
}
