<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Patient\Models\Patient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->with('currentDemographics')
            ->when(
                $request->filled('search'),
                fn ($query) => $query->search($request->string('search'))
            )
            ->paginate(20);

        return PatientResource::collection($patients);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create([
            'uuid' => (string) str()->uuid(),
            'mrn' => $request->mrn,
            'status' => 'active',
        ]);

        return response()->json([
            'data' => new PatientResource($patient),
        ], 201);
    }

    public function show(Patient $patient): PatientResource
    {
        $patient->load([
            'currentDemographics',
            'encounters',
            'allergies',
            'conditions',
            'latestVitals',
        ]);

        return new PatientResource($patient);
    }

    public function update(
        UpdatePatientRequest $request,
        Patient $patient
    ): PatientResource {
        $patient->update($request->validated());

        return new PatientResource($patient->fresh());
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return response()->json([
            'message' => 'Patient archived successfully.',
        ]);
    }
}