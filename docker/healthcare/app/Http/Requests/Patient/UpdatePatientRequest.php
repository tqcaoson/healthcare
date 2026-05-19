<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'in:active,inactive,deceased',
            ],

            'deceased_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}