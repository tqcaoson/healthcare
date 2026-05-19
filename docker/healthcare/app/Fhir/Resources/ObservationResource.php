<?php

namespace App\Fhir\Resources;

use App\Domain\Result\Models\LabResult;

class ObservationResource
{
    public static function make(
        LabResult $result
    ): array {

        return [
            'resourceType' => 'Observation',

            'id' => $result->uuid,

            'status' => $result->status,

            'code' => [
                'coding' => [
                    [
                        'system' => 'http://loinc.org',
                        'code' => $result->loinc_code,
                        'display' => $result->loinc_display,
                    ]
                ]
            ],

            'valueQuantity' => [
                'value' => $result->value_quantity,
                'unit' => $result->value_unit,
            ],

            'effectiveDateTime' => $result->resulted_at?->toIso8601String(),
        ];
    }
}