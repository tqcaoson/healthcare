<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'require.mfa',
    'auto.logout',
])->group(function () {

    Route::get('/dashboard', DashboardController::class);

    Route::resource('patients', PatientController::class);

    Route::resource('encounters', EncounterController::class);

    Route::resource('orders', OrderController::class);

    Route::resource('results', ResultController::class);

    Route::resource('appointments', AppointmentController::class);
});
