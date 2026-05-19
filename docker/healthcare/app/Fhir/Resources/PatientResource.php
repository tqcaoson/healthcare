<?php

namespace App\Fhir\Resources;

use App\Domain\Patient\Models\Patient;

class PatientResource
{
    public static function make(Patient $patient): array
    {
        return [
            'resourceType' => 'Patient',

            'id' => $patient->uuid,

            'identifier' => [
                [
                    'system' => 'urn:mrn',
                    'value' => $patient->mrn,
                ]
            ],

            'name' => [
                [
                    'family' => $patient->currentDemographics?->family_name,
                    'given' => [
                        $patient->currentDemographics?->given_name,
                    ]
                ]
            ],

            'gender' => $patient->currentDemographics?->gender,

            'birthDate' => $patient->currentDemographics?->birth_date,
        ];
    }
}