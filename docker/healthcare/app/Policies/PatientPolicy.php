<?php

namespace App\Policies;

use App\Domain\Patient\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patients.view');
    }

    public function view(
        User $user,
        Patient $patient
    ): bool {

        return $user->can('patients.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patients.create');
    }

    public function update(
        User $user,
        Patient $patient
    ): bool {

        return $user->can('patients.update');
    }

    public function delete(
        User $user,
        Patient $patient
    ): bool {

        return $user->can('patients.delete');
    }
}