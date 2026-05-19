<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNewUser
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'username' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:12'],
        ])->validate();

        return User::create([
            'uuid' => (string) str()->uuid(),
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'status' => 'active',
        ]);
    }
}