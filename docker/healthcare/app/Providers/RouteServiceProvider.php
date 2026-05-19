<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(
                base_path('routes/api.php')
            );

        Route::middleware('api')
            ->prefix('fhir')
            ->group(
                base_path('routes/fhir.php')
            );
    }
}