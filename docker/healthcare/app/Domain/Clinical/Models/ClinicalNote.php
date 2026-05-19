<?php

namespace App\Domain\Clinical\Models;

use App\Domain\Common\Models\PhiModel;

class ClinicalNote extends PhiModel
{
    protected $table = 'clinical_notes';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'author_id',
        'loinc_type_code',
        'loinc_type_display',
        'note_type',
        'content',
        'content_type',
        'status',
        'attested_by',
        'attested_at',
        'documented_at',
    ];

    protected $casts = [
        'attested_at' => 'datetime',
        'documented_at' => 'datetime',
    ];
}