<?php

use App\Http\Controllers\Api\V1\AllergyController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\EncounterController;
use App\Http\Controllers\Api\V1\MARScanController;
use App\Http\Controllers\Api\V1\MedicationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ResultController;
use App\Http\Controllers\Api\V1\VitalSignController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',
        'require.mfa',
        'audit.phi',
    ])
    ->group(function () {

        Route::apiResource('patients', PatientController::class);

        Route::apiResource('encounters', EncounterController::class);

        Route::apiResource('orders', OrderController::class);

        Route::apiResource('results', ResultController::class);

        Route::apiResource('medications', MedicationController::class);

        // MAR Scan — Medication Administration Record with barcode verification
        Route::prefix('mar')->name('mar.')->group(function () {
            Route::get('schedule', [MARScanController::class, 'schedule'])->name('schedule');
            Route::post('scan/patient', [MARScanController::class, 'scanPatient'])->name('scan.patient');
            Route::post('scan/medication', [MARScanController::class, 'scanMedication'])->name('scan.medication');
            Route::post('administer', [MARScanController::class, 'administer'])->name('administer');
        });

        Route::apiResource('vital-signs', VitalSignController::class);

        Route::apiResource('allergies', AllergyController::class);

        Route::apiResource('appointments', AppointmentController::class);

        Route::get(
            'reports/quality',
            [ReportController::class, 'quality']
        );

        Route::get(
            'dashboard',
            \App\Http\Controllers\Api\V1\DashboardController::class
        );
    });