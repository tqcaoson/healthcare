<?php

namespace App\Services\SmartOnFhir;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class SmartTokenService
{
    public function issue(
        User $user,
        array $scopes = []
    ): NewAccessToken {

        return $user->createToken(
            'smart-on-fhir',
            $scopes
        );
    }
}