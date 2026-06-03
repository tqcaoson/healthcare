<?php

namespace App\Domain\Scheduling\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends PhiModel
{

    protected $table = 'appointments';

    protected $fillable = [
        'uuid',
        'patient_id',
        'provider_id',
        'department_id',
        'room_id',
        'status',
        'appointment_type',
        'reason',
        'start_at',
        'end_at',
        'duration_minutes',
        'arrived_at',
        'roomed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'start_at'     => 'datetime',
        'end_at'       => 'datetime',
        'arrived_at'   => 'datetime',
        'roomed_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}