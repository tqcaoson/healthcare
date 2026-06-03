@extends('layouts.app')
@section('page-title', 'Nhập kết quả xét nghiệm')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('results.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Nhập kết quả xét nghiệm</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('results.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
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
                <label class="block text-sm font-medium text-slate-700 mb-1">LOINC Code *</label>
                <input type="text" name="loinc_code" value="{{ old('loinc_code') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: 2345-7" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tên xét nghiệm *</label>
                <input type="text" name="loinc_display" value="{{ old('loinc_display') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: Glucose [Mass/volume] in Blood" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Giá trị số</label>
                <input type="number" step="0.001" name="value_quantity" value="{{ old('value_quantity') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Đơn vị</label>
                <input type="text" name="value_unit" value="{{ old('value_unit') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: mg/dL, mmol/L">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cờ bất thường *</label>
                <select name="abnormal_flag"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="N"    {{ old('abnormal_flag', 'N') === 'N'    ? 'selected' : '' }}>N — Bình thường</option>
                    <option value="H"    {{ old('abnormal_flag') === 'H'    ? 'selected' : '' }}>H — Cao</option>
                    <option value="L"    {{ old('abnormal_flag') === 'L'    ? 'selected' : '' }}>L — Thấp</option>
                    <option value="HH"   {{ old('abnormal_flag') === 'HH'   ? 'selected' : '' }}>HH — Rất cao (Critical)</option>
                    <option value="LL"   {{ old('abnormal_flag') === 'LL'   ? 'selected' : '' }}>LL — Rất thấp (Critical)</option>
                    <option value="A"    {{ old('abnormal_flag') === 'A'    ? 'selected' : '' }}>A — Bất thường</option>
                    <option value="null" {{ old('abnormal_flag') === 'null' ? 'selected' : '' }}>N/A</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian có kết quả *</label>
                <input type="datetime-local" name="resulted_at"
                       value="{{ old('resulted_at', now()->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('results.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">Huỷ</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Lưu kết quả
            </button>
        </div>
    </form>
</div>
@endsection
