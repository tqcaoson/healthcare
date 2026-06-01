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
});

Route::redirect('/', '/dashboard');
