# Routes

## API v1 — /api/v1/*

```php
Route::middleware(['auth:sanctum', 'require.mfa', 'audit.phi', 'auto.logout'])
    ->prefix('v1')
    ->group(function () {

        // Patients
        Route::apiResource('patients', PatientController::class);
        Route::get('patients/{patient}/summary', [PatientController::class, 'summary']);
        Route::get('patients/{patient}/timeline', [PatientController::class, 'timeline']);

        // Encounters
        Route::apiResource('patients/{patient}/encounters', EncounterController::class);
        Route::post('encounters/{encounter}/admit', [AdtController::class, 'admit']);
        Route::post('encounters/{encounter}/discharge', [AdtController::class, 'discharge']);
        Route::post('encounters/{encounter}/transfer', [AdtController::class, 'transfer']);

        // Clinical data
        Route::apiResource('encounters/{encounter}/vital-signs', VitalSignController::class);
        Route::apiResource('patients/{patient}/allergies', AllergyController::class);
        Route::apiResource('patients/{patient}/conditions', ConditionController::class);
        Route::apiResource('encounters/{encounter}/notes', ClinicalNoteController::class);

        // Orders (CPOE)
        Route::apiResource('encounters/{encounter}/orders', OrderController::class);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('orders/{order}/cds-alerts', [OrderController::class, 'cdsAlerts']);
        Route::post('orders/{order}/override-alert', [OrderController::class, 'overrideAlert']);

        // Results
        Route::get('patients/{patient}/lab-results', [LabResultController::class, 'index']);
        Route::get('lab-results/{result}', [LabResultController::class, 'show']);
        Route::post('lab-results/{result}/acknowledge', [LabResultController::class, 'acknowledge']);

        // Medications
        Route::apiResource('encounters/{encounter}/medication-administrations', MedicationAdministrationController::class);
        Route::get('patients/{patient}/medication-reconciliation', [MedicationController::class, 'reconciliation']);

        // Scheduling
        Route::apiResource('appointments', AppointmentController::class);
        Route::post('appointments/{appointment}/arrive', [AppointmentController::class, 'arrive']);
        Route::get('providers/{provider}/slots', [SchedulingController::class, 'availableSlots']);

        // Bed management
        Route::get('beds', [BedController::class, 'index']);
        Route::patch('beds/{bed}/status', [BedController::class, 'updateStatus']);

        // Billing
        Route::apiResource('encounters/{encounter}/charge-items', ChargeItemController::class);
        Route::apiResource('claims', ClaimController::class);

        // Reports
        Route::get('reports/census', [ReportController::class, 'census']);
        Route::get('reports/quality-metrics', [ReportController::class, 'qualityMetrics']);
        Route::get('reports/lab-turnaround', [ReportController::class, 'labTurnaround']);
        Route::get('reports/medication-errors', [ReportController::class, 'medicationErrors']);
    });
```

## Telehealth Routes

```php
Route::middleware(['auth:sanctum', 'audit.phi'])->prefix('v1/telehealth')->group(function () {
    Route::post('sessions', [TelehealthController::class, 'create']);
    Route::get('sessions/{uuid}', [TelehealthController::class, 'show']);
    Route::post('sessions/{uuid}/start', [TelehealthController::class, 'start']);
    Route::post('sessions/{uuid}/end', [TelehealthController::class, 'end']);
    Route::post('sessions/{uuid}/patient-arrived', [TelehealthController::class, 'patientArrived']);
    Route::get('sessions/{uuid}/waiting-room', [TelehealthController::class, 'waitingRoom']);
});

// Patient-facing — token auth (không cần Sanctum account)
Route::post('telehealth/join', [TelehealthController::class, 'patientJoin'])
    ->middleware('telehealth.token');
```

## Rate Limiting

```php
RateLimiter::for('phi-access', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id);
});

RateLimiter::for('auth', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),            // brute force protection
        Limit::perDay(100)->by($request->user()?->id ?? $request->ip()),
    ];
});
```
