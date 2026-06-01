<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Clinical\Models\Allergy;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllergyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allergies = Allergy::query()
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->paginate(20);

        return response()->json(['data' => $allergies]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'category' => 'required|in:food,medication,environment,biologic',
            'substance_name' => 'required|string',
            'reaction_manifestation' => 'nullable|string',
            'reaction_severity' => 'nullable|in:mild,moderate,severe',
            'onset_date' => 'nullable|date',
        ]);

        $allergy = Allergy::create([
            'clinical_status' => 'active',
            'verification' => 'unconfirmed',
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
            ...$validated,
        ]);

        return response()->json(['data' => $allergy], 201);
    }

    public function show(Allergy $allergy): JsonResponse
    {
        return response()->json(['data' => $allergy]);
    }

    public function update(Request $request, Allergy $allergy): JsonResponse
    {
        $validated = $request->validate([
            'clinical_status' => 'sometimes|in:active,inactive,resolved',
            'verification' => 'sometimes|in:unconfirmed,confirmed,refuted,entered-in-error',
            'reaction_severity' => 'sometimes|nullable|in:mild,moderate,severe',
            'note' => 'sometimes|nullable|string',
        ]);

        $allergy->update($validated);

        return response()->json(['data' => $allergy->fresh()]);
    }

    public function destroy(Allergy $allergy): JsonResponse
    {
        $allergy->update(['clinical_status' => 'resolved']);

        return response()->json(['message' => 'Allergy resolved.']);
    }
}
