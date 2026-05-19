<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UpdateUserPassword
{
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required'],
            'password' => ['required', 'min:12'],
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}