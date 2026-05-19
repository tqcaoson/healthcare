<?php

namespace App\Http\Controllers\Fhir\R4;

use App\Fhir\Resources\PatientResource;
use App\Http\Controllers\Controller;
use App\Services\Fhir\FhirBundleBuilder;
use App\Services\Fhir\FhirSearchService;
use Illuminate\Http\Request;

class PatientSearchController extends Controller
{
    public function __invoke(
        Request $request,
        FhirSearchService $search,
        FhirBundleBuilder $bundle
    ) {

        $patients = $search->patientSearch(
            $request->all()
        );

        return response()->json(
            $bundle->collection(
                $patients
                    ->map(fn ($patient) =>
                        PatientResource::make($patient)
                    )
                    ->all()
            )
        );
    }
}