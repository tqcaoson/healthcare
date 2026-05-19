<?php

namespace App\Providers;

use App\Actions\Auth\CreateNewUser;
use App\Actions\Auth\ResetUserPassword;
use App\Actions\Auth\UpdateUserPassword;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(
            CreateNewUser::class
        );

        Fortify::updateUserPasswordsUsing(
            UpdateUserPassword::class
        );

        Fortify::resetUserPasswordsUsing(
            ResetUserPassword::class
        );
    }
}