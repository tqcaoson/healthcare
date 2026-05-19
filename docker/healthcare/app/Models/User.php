<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use HasRoles;
    use HasUuids;

    protected $fillable = [
        'uuid',
        'username',
        'email',
        'password',
        'employee_id',
        'department_id',
        'provider_npi',
        'mfa_enabled',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mfa_enabled' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}