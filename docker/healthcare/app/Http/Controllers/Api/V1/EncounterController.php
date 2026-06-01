<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Encounter\Models\Encounter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $encounters = Encounter::query()
            ->with(['patient', 'diagnoses'])
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->paginate(20);

        return response()->json(['data' => $encounters]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'encounter_type' => 'required|in:inpatient,outpatient,emergency,telehealth',
            'department_id' => 'required|integer',
            'class' => 'required|in:IMP,AMB,EMER,HH',
        ]);

        $encounter = Encounter::create([
            'uuid' => (string) str()->uuid(),
            'status' => 'planned',
            'period_start' => now(),
            ...$validated,
        ]);

        return response()->json(['data' => $encounter], 201);
    }

    public function show(Encounter $encounter): JsonResponse
    {
        $encounter->load(['patient', 'diagnoses']);

        return response()->json(['data' => $encounter]);
    }

    public function update(Request $request, Encounter $encounter): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'discharge_disposition' => 'sometimes|nullable|string',
            'period_end' => 'sometimes|nullable|date',
        ]);

        $encounter->update($validated);

        return response()->json(['data' => $encounter->fresh()]);
    }

    public function destroy(Encounter $encounter): JsonResponse
    {
        $encounter->delete();

        return response()->json(['message' => 'Encounter archived.']);
    }
}
