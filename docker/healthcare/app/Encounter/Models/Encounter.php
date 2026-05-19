<?php

namespace App\Domain\Encounter\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encounter extends PhiModel
{
    use HasFactory;
    use HasUuids;

    protected $table = 'encounters';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_type',
        'status',
        'class',
        'admission_source',
        'discharge_disposition',
        'period_start',
        'period_end',
        'attending_provider_id',
        'admitting_provider_id',
        'department_id',
        'bed_id',
        'reason_text',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(EncounterDiagnosis::class);
    }
}