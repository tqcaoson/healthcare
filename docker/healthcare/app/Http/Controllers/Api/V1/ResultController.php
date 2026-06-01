<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Result\Models\LabResult;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $results = LabResult::query()
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('encounter_id'), fn ($q) => $q->where('encounter_id', $request->encounter_id))
            ->orderByDesc('resulted_at')
            ->paginate(20);

        return response()->json(['data' => $results]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'loinc_code' => 'required|string',
            'loinc_display' => 'required|string',
            'value_quantity' => 'nullable|numeric',
            'value_unit' => 'nullable|string',
            'value_string' => 'nullable|string',
            'resulted_at' => 'required|date',
            'status' => 'sometimes|in:registered,preliminary,final,corrected,amended,cancelled',
        ]);

        $result = LabResult::create([
            'uuid' => (string) str()->uuid(),
            'status' => 'final',
            ...$validated,
        ]);

        return response()->json(['data' => $result], 201);
    }

    public function show(LabResult $result): JsonResponse
    {
        return response()->json(['data' => $result]);
    }

    public function update(Request $request, LabResult $result): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:registered,preliminary,final,corrected,amended,cancelled',
            'acknowledged_by' => 'sometimes|exists:users,id',
            'acknowledged_at' => 'sometimes|date',
        ]);

        $result->update($validated);

        return response()->json(['data' => $result->fresh()]);
    }

    public function destroy(LabResult $result): JsonResponse
    {
        $result->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Result cancelled.']);
    }
}
