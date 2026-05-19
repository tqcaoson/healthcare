<?php

namespace App\Domain\Scheduling\Models;

use App\Domain\Common\Models\PhiModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Appointment extends PhiModel
{
    use HasUuids;

    protected $table = 'appointments';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'provider_id',
        'department_id',
        'appointment_type',
        'status',
        'scheduled_start',
        'scheduled_end',
        'arrival_time',
        'checkin_time',
        'checkout_time',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'arrival_time' => 'datetime',
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime',
    ];
}