<?php

namespace App\Domain\Documents\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalDocument extends Model
{
    protected $table = 'clinical_documents';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'document_type',
        'title',
        'mime_type',
        'disk',
        'path',
        'checksum',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}