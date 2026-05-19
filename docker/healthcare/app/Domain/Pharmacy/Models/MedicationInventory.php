<?php

namespace App\Domain\Pharmacy\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationInventory extends Model
{
    protected $table = 'medication_inventory';

    protected $fillable = [
        'rxnorm_code',
        'drug_name',
        'lot_number',
        'expiration_date',
        'quantity_on_hand',
        'reorder_level',
        'storage_location',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];
}