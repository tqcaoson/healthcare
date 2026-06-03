@extends('layouts.app')
@section('page-title', 'Ghi nhận thuốc')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('medications.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Ghi nhận thuốc đã dùng</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('medications.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Bệnh nhân *</label>
            <select name="patient_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <option value="">-- Chọn bệnh nhân --</option>
                @foreach($patients as $patient)
                    @php $d = $patient->currentDemographics; @endphp
                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                        {{ $d?->family_name }} {{ $d?->given_name }} ({{ $patient->mrn }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tên thuốc *</label>
                <input type="text" name="drug_name" value="{{ old('drug_name') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: Paracetamol, Metformin" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mã RxNorm</label>
                <input type="text" name="rxnorm_code" value="{{ old('rxnorm_code') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: 161">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Liều *</label>
                <input type="number" step="0.001" name="dose_value" value="{{ old('dose_value') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Đơn vị *</label>
                <input type="text" name="dose_unit" value="{{ old('dose_unit') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="mg, mL, units" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Đường dùng *</label>
                <select name="route"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="oral"     {{ old('route', 'oral') === 'oral'     ? 'selected' : '' }}>Uống (oral)</option>
                    <option value="IV"       {{ old('route') === 'IV'       ? 'selected' : '' }}>Tiêm tĩnh mạch (IV)</option>
                    <option value="IM"       {{ old('route') === 'IM'       ? 'selected' : '' }}>Tiêm bắp (IM)</option>
                    <option value="SC"       {{ old('route') === 'SC'       ? 'selected' : '' }}>Tiêm dưới da (SC)</option>
                    <option value="topical"  {{ old('route') === 'topical'  ? 'selected' : '' }}>Bôi ngoài da</option>
                    <option value="inhaled"  {{ old('route') === 'inhaled'  ? 'selected' : '' }}>Hít</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian dùng *</label>
            <input type="datetime-local" name="administered_at"
                   value="{{ old('administered_at', now()->format('Y-m-d\TH:i')) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_prn" id="is_prn" value="1" {{ old('is_prn') ? 'checked' : '' }}
                   class="rounded border-slate-300">
            <label for="is_prn" class="text-sm text-slate-700">Thuốc dùng khi cần (PRN)</label>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('medications.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">Huỷ</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Lưu
            </button>
        </div>
    </form>
</div>
@endsection
