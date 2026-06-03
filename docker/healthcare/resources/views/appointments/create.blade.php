@extends('layouts.app')
@section('page-title', 'Đặt lịch hẹn')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('appointments.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Đặt lịch hẹn mới</h1>
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

    <form method="POST" action="{{ route('appointments.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
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
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại hẹn</label>
                <select name="appointment_type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="routine"   {{ old('appointment_type', 'routine') === 'routine'   ? 'selected' : '' }}>Định kỳ</option>
                    <option value="followup"  {{ old('appointment_type') === 'followup'  ? 'selected' : '' }}>Tái khám</option>
                    <option value="emergency" {{ old('appointment_type') === 'emergency' ? 'selected' : '' }}>Cấp cứu</option>
                    <option value="checkup"   {{ old('appointment_type') === 'checkup'   ? 'selected' : '' }}>Khám tổng quát</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Lý do</label>
                <input type="text" name="reason" value="{{ old('reason') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="vd: Kiểm tra huyết áp">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian bắt đầu *</label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian kết thúc *</label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('appointments.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">Huỷ</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Đặt lịch
            </button>
        </div>
    </form>
</div>
@endsection
