<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'mrn' => $this->mrn,
            'status' => $this->status,

            'demographics' => [
                'given_name' => $this->currentDemographics?->given_name,
                'family_name' => $this->currentDemographics?->family_name,
                'birth_date' => $this->currentDemographics?->birth_date,
                'gender' => $this->currentDemographics?->gender,
                'phone' => $this->currentDemographics?->phone,
                'email' => $this->currentDemographics?->email,
            ],

            'latest_vitals' => $this->whenLoaded(
                'latestVitals',
                fn () => [
                    'systolic_bp' => $this->latestVitals?->systolic_bp,
                    'diastolic_bp' => $this->latestVitals?->diastolic_bp,
                    'heart_rate' => $this->latestVitals?->heart_rate,
                    'temperature' => $this->latestVitals?->temperature,
                    'spo2' => $this->latestVitals?->spo2,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}