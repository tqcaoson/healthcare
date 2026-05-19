<?php

namespace App\Domain\Order\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends PhiModel
{
    use HasFactory;
    use HasUuids;

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
}