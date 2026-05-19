<?php

namespace App\Domain\Radiology\Models;

use App\Domain\Common\Models\PhiModel;

class RadiologyStudy extends PhiModel
{
    protected $table = 'radiology_studies';

    protected $fillable = [
        'uuid',
        'patient_id',
        'encounter_id',
        'order_id',
        'accession_number',
        'modality',
        'body_site',
        'status',
        'priority',
        'scheduled_at',
        'performed_at',
        'reported_at',
        'radiologist_id',
        'dicom_study_uid',
        'pacs_url',
        'report_text',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'performed_at' => 'datetime',
        'reported_at' => 'datetime',
    ];
}