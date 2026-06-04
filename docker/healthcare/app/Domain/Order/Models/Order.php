<?php

namespace App\Domain\Order\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends PhiModel
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'order_number',
        'encounter_id',
        'patient_id',
        'order_type',
        'status',
        'priority',
        'ordered_by',
        'ordered_at',
        'scheduled_for',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'clinical_indication',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function medicationOrder(): HasOne
    {
        return $this->hasOne(MedicationOrder::class);
    }
}