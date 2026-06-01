<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Clinical\Models\VitalSign;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vitals = VitalSign::query()
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('encounter_id'), fn ($q) => $q->where('encounter_id', $request->encounter_id))
            ->orderByDesc('recorded_at')
            ->paginate(20);

        return response()->json(['data' => $vitals]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'required|exists:encounters,id',
            'systolic_bp' => 'nullable|integer',
            'diastolic_bp' => 'nullable|integer',
            'heart_rate' => 'nullable|integer',
            'respiratory_rate' => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'spo2' => 'nullable|integer|between:0,100',
            'weight_kg' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'pain_score' => 'nullable|integer|between:0,10',
        ]);

        $vital = VitalSign::create([
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
            ...$validated,
        ]);

        return response()->json(['data' => $vital], 201);
    }

    public function show(VitalSign $vitalSign): JsonResponse
    {
        return response()->json(['data' => $vitalSign]);
    }

    public function update(Request $request, VitalSign $vitalSign): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'sometimes|nullable|string',
        ]);

        $vitalSign->update($validated);

        return response()->json(['data' => $vitalSign->fresh()]);
    }

    public function destroy(VitalSign $vitalSign): JsonResponse
    {
        $vitalSign->delete();

        return response()->json(['message' => 'Vital sign removed.']);
    }
}
