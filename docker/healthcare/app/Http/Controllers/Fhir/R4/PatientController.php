<?php

namespace App\Http\Controllers\Fhir\R4;

use App\Domain\Patient\Models\Patient;
use App\Fhir\Resources\PatientResource;
use App\Http\Controllers\Controller;

class PatientController extends Controller
{
    public function show(Patient $patient)
    {
        return response()->json(
            PatientResource::make($patient)
        );
    }
}