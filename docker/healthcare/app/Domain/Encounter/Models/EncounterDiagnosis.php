<?php

namespace App\Domain\Encounter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterDiagnosis extends Model
{
    protected $table = 'encounter_diagnoses';
    public $timestamps = false;

    protected $fillable = [
        'encounter_id',
        'icd10_code',
        'icd10_display',
        'diagnosis_type',
        'rank',
        'onset_date',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'onset_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }
}