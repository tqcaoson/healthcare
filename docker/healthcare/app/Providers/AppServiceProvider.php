<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::useBuildDirectory('dist');
        Vite::useManifestFilename('.vite/manifest.json');

        RateLimiter::for('api', function ($request) {

            return Limit::perMinute(120)
                ->by(
                    $request->user()?->id
                    ?: $request->ip()
                );
        });
    }
}