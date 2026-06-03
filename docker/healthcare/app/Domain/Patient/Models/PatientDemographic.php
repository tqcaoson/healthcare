<?php

namespace App\Domain\Patient\Models;

use App\Domain\Common\Models\PhiModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDemographic extends PhiModel
{
    protected $table = 'patient_demographics';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'version',
        'given_name',
        'family_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address_line',
        'city',
        'postal_code',
        'language',
        'is_current',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'changed_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
