<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mrn' => [
                'required',
                'string',
                'max:20',
                'unique:patients,mrn',
            ],

            'given_name' => [
                'required',
                'string',
                'max:100',
            ],

            'family_name' => [
                'required',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'required',
                'date',
            ],

            'gender' => [
                'required',
                'in:male,female,other,unknown',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ];
    }
}