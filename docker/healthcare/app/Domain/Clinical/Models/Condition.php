<?php

namespace App\Domain\Clinical\Models;

use App\Domain\Common\Models\PhiModel;

class Condition extends PhiModel
{
    protected $table = 'conditions';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'icd10_code',
        'icd10_display',
        'snomed_code',
        'snomed_display',
        'free_text',
        'category',
        'clinical_status',
        'verification',
        'severity',
        'onset_date',
        'abatement_date',
        'recorded_at',
        'recorded_by',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'onset_date' => 'date',
        'abatement_date' => 'date',
    ];
}