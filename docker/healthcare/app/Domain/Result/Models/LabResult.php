<?php

namespace App\Domain\Result\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends PhiModel
{
    protected $table = 'lab_results';

    protected $fillable = [
        'uuid',
        'order_id',
        'patient_id',
        'encounter_id',
        'loinc_code',
        'loinc_display',
        'value_quantity',
        'value_unit',
        'value_string',
        'abnormal_flag',
        'status',
        'collected_at',
        'received_at',
        'resulted_at',
        'is_critical',
    ];

    protected $casts = [
        'value_string' => 'encrypted', // PHI narrative — must be encrypted at rest
        'collected_at' => 'datetime',
        'received_at'  => 'datetime',
        'resulted_at'  => 'datetime',
        'is_critical'  => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}