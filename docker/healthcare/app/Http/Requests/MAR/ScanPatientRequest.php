<?php

namespace App\Http\Requests\MAR;

use Illuminate\Foundation\Http\FormRequest;

class ScanPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['nurse', 'physician', 'pharmacist', 'superadmin']);
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:100'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
        ];
    }
}
