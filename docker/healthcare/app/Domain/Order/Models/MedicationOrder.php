<?php

namespace App\Domain\Order\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Medication\Models\MedicationAdministration;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationOrder extends PhiModel
{
    protected $table = 'medication_orders';

    protected $fillable = [
        'order_id',
        'rxnorm_code',
        'drug_name',
        'dose_value',
        'dose_unit',
        'route',
        'frequency',
        'duration_days',
        'start_date',
        'end_date',
        'instructions',
        'substitution_allowed',
        'verified_by_pharmacist',
        'verified_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
        'substitution_allowed' => 'boolean',
        'dose_value' => 'decimal:3',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function administrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class, 'order_id', 'order_id');
    }

    public function isVerified(): bool
    {
        return $this->verified_by_pharmacist !== null;
    }
}
