<?php

namespace App\Http\Requests\MAR;

use Illuminate\Foundation\Http\FormRequest;

class RecordAdministrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['nurse', 'physician', 'admin', 'superadmin']);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'encounter_id' => ['required', 'integer', 'exists:encounters,id'],
            'patient_barcode' => ['required', 'string', 'max:100'],
            'medication_barcode' => ['required', 'string', 'max:255'],
            'drug_name' => ['required', 'string', 'max:255'],
            'dose_value' => ['required', 'numeric', 'min:0.001'],
            'dose_unit' => ['required', 'string', 'max:20'],
            'route' => ['required', 'string', 'in:oral,IV,IM,SC,topical,inhaled,rectal,sublingual,transdermal,other'],
            'rxnorm_code' => ['nullable', 'string', 'max:20'],
            'scheduled_at' => ['nullable', 'date'],
            'is_prn' => ['boolean'],
            'prn_indication' => ['nullable', 'required_if:is_prn,true', 'string', 'max:500'],
            'site' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'wasted_amount' => ['nullable', 'numeric', 'min:0'],
            'waste_witnessed_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
