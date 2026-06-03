<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
        ->name('dashboard');

    Route::resource('patients',    \App\Http\Controllers\Web\PatientController::class);
    Route::resource('encounters',  \App\Http\Controllers\Web\EncounterController::class);
    Route::resource('orders',      \App\Http\Controllers\Web\OrderController::class);
    Route::resource('results',     \App\Http\Controllers\Web\ResultController::class);
    Route::resource('appointments', \App\Http\Controllers\Web\AppointmentController::class);

    Route::get('medications', [\App\Http\Controllers\Web\MedicationController::class, 'index'])->name('medications.index');
    Route::get('medications/create', [\App\Http\Controllers\Web\MedicationController::class, 'create'])->name('medications.create');
    Route::post('medications', [\App\Http\Controllers\Web\MedicationController::class, 'store'])->name('medications.store');
    Route::get('medications/{id}', [\App\Http\Controllers\Web\MedicationController::class, 'show'])->name('medications.show');

    Route::get('reports', [\App\Http\Controllers\Web\ReportController::class, 'index'])->name('reports.index');
});

Route::redirect('/', '/dashboard');
