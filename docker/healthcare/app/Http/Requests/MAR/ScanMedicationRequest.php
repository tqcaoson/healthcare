<?php

namespace App\Http\Requests\MAR;

use Illuminate\Foundation\Http\FormRequest;

class ScanMedicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['nurse', 'physician', 'pharmacist', 'superadmin']);
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:255'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ];
    }
}
