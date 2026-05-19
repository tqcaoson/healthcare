<?php

use App\Http\Controllers\Fhir\R4\PatientController;
use Illuminate\Support\Facades\Route;

Route::prefix('R4')
    ->middleware([
        'auth:sanctum',
    ])
    ->group(function () {

        Route::get(
            'Patient/{patient}',
            [PatientController::class, 'show']
        );
    });

Route::get(
    'Patient',
    \App\Http\Controllers\Fhir\R4\PatientSearchController::class
);