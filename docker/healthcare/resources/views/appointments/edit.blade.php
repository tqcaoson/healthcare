@extends('layouts.app')
@section('page-title', 'Chỉnh sửa lịch hẹn')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('appointments.show', $appointment->id) }}" class="text-slate-500 hover:text-slate-800">← Chi tiết</a>
        <h1 class="text-2xl font-bold text-slate-800">Chỉnh sửa lịch hẹn</h1>
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

    <form method="POST" action="{{ route('appointments.update', $appointment->id) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái *</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    @foreach(['booked','arrived','fulfilled','cancelled','noshow','checked-in'] as $s)
                        <option value="{{ $s }}" {{ old('status', $appointment->status) === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại hẹn</label>
                <select name="appointment_type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['routine'=>'Định kỳ','followup'=>'Tái khám','emergency'=>'Cấp cứu','checkup'=>'Khám tổng quát'] as $val => $label)
                        <option value="{{ $val }}" {{ old('appointment_type', $appointment->appointment_type) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bắt đầu *</label>
                <input type="datetime-local" name="start_at"
                       value="{{ old('start_at', $appointment->start_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kết thúc *</label>
                <input type="datetime-local" name="end_at"
                       value="{{ old('end_at', $appointment->end_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Lý do</label>
            <input type="text" name="reason" value="{{ old('reason', $appointment->reason) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Lý do huỷ (nếu huỷ)</label>
            <input type="text" name="cancel_reason" value="{{ old('cancel_reason', $appointment->cancel_reason) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('appointments.show', $appointment->id) }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">Huỷ</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Cập nhật
            </button>
        </div>
    </form>
</div>
@endsection
