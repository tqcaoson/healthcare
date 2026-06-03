@extends('layouts.app')
@section('page-title', 'Tạo lần khám mới')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('encounters.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Tạo lần khám mới</h1>
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

    <form method="POST" action="{{ route('encounters.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Bệnh nhân *</label>
            <select name="patient_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <option value="">-- Chọn bệnh nhân --</option>
                @foreach($patients as $patient)
                    @php $d = $patient->currentDemographics; @endphp
                    <option value="{{ $patient->id }}"
                        {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                        {{ $d?->family_name }} {{ $d?->given_name }} ({{ $patient->mrn }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại khám *</label>
                <select name="encounter_type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="outpatient" {{ old('encounter_type') === 'outpatient' ? 'selected' : '' }}>Ngoại trú</option>
                    <option value="inpatient"  {{ old('encounter_type') === 'inpatient'  ? 'selected' : '' }}>Nội trú</option>
                    <option value="emergency"  {{ old('encounter_type') === 'emergency'  ? 'selected' : '' }}>Cấp cứu</option>
                    <option value="telehealth" {{ old('encounter_type') === 'telehealth' ? 'selected' : '' }}>Telehealth</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Class *</label>
                <select name="class"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="AMB" {{ old('class') === 'AMB' ? 'selected' : '' }}>AMB - Ambulatory</option>
                    <option value="IMP" {{ old('class') === 'IMP' ? 'selected' : '' }}>IMP - Inpatient</option>
                    <option value="EMER" {{ old('class') === 'EMER' ? 'selected' : '' }}>EMER - Emergency</option>
                    <option value="HH" {{ old('class') === 'HH' ? 'selected' : '' }}>HH - Home Health</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian bắt đầu *</label>
            <input type="datetime-local" name="period_start"
                   value="{{ old('period_start', now()->format('Y-m-d\TH:i')) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Lý do khám</label>
            <textarea name="reason_text" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Mô tả lý do khám...">{{ old('reason_text') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('encounters.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">
                Huỷ
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Tạo lần khám
            </button>
        </div>
    </form>
</div>
@endsection
