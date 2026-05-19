<?php

use App\Http\Controllers\SmartOnFhir\AuthorizeController;
use App\Http\Controllers\SmartOnFhir\TokenController;
use Illuminate\Support\Facades\Route;

Route::get(
    'authorize',
    AuthorizeController::class
);

Route::post(
    'token',
    TokenController::class
);