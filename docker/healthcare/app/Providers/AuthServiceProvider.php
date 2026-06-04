<?php

namespace App\Providers;

use App\Domain\Patient\Models\Patient;
use App\Models\User;
use App\Policies\PatientPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Patient::class => PatientPolicy::class,
        User::class    => UserPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}