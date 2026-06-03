<?php

namespace App\Domain\Clinical\Models;

use App\Domain\Common\Models\PhiModel;

class Allergy extends PhiModel
{
    protected $table = 'allergies';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'category',
        'clinical_status',
        'verification',
        'rxnorm_code',
        'substance_name',
        'reaction_manifestation',
        'reaction_severity',
        'onset_date',
        'recorded_by',
        'recorded_at',
        'note',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'onset_date' => 'date',
    ];
}