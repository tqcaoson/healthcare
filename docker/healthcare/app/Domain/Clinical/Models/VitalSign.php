<?php

namespace App\Domain\Clinical\Models;

use App\Domain\Common\Models\PhiModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends PhiModel
{
    protected $table = 'vital_signs';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'encounter_id',
        'recorded_by',
        'recorded_at',
        'systolic_bp',
        'diastolic_bp',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'temp_route',
        'spo2',
        'weight_kg',
        'height_cm',
        'bmi',
        'pain_score',
        'gcs_total',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $vital) {

            if (
                $vital->weight_kg &&
                $vital->height_cm
            ) {
                $heightM = $vital->height_cm / 100;

                $vital->bmi = round(
                    $vital->weight_kg / ($heightM * $heightM),
                    2
                );
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            \App\Domain\Patient\Models\Patient::class
        );
    }
}