<?php

namespace App\Domain\Billing\Models;

use App\Domain\Common\Models\PhiModel;

class Invoice extends PhiModel
{
    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'invoice_number',
        'status',
        'currency',
        'subtotal',
        'tax',
        'discount',
        'total',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
}