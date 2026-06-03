<?php

namespace App\Domain\Medication\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationAdministration extends PhiModel
{
    protected $table = 'medication_administrations';

    protected $fillable = [
        'uuid',
        'order_id',
        'patient_id',
        'encounter_id',
        'status',
        'patient_verified_by_barcode',
        'medication_verified_by_barcode',
        'rxnorm_code',
        'drug_name',
        'dose_value',
        'dose_unit',
        'route',
        'scheduled_at',
        'administered_at',
        'administered_by',
        'is_prn',
        'prn_indication',
        'prn_effectiveness',
        'site',
        'wasted_amount',
        'waste_witnessed_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'administered_at' => 'datetime',
        'is_prn' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}