<?php

namespace App\Policies;

use App\Domain\Patient\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    private function canAccessPatients(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse', 'clerk', 'pharmacist']);
    }

    public function viewAny(User $user): bool
    {
        return $this->canAccessPatients($user);
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->canAccessPatients($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'clerk']);
    }

    public function update(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'clerk']);
    }

    public function delete(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['admin']);
    }
}
