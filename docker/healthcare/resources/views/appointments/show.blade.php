@extends('layouts.app')
@section('page-title', 'Chi tiết lịch hẹn')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('appointments.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
            <h1 class="text-2xl font-bold text-slate-800">Chi tiết lịch hẹn</h1>
        </div>
        <a href="{{ route('appointments.edit', $appointment->id) }}"
           class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Chỉnh sửa
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $appointment->patient?->currentDemographics; @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <div class="font-bold text-blue-900">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="text-sm text-blue-700 mt-1">MRN: {{ $appointment->patient?->mrn }}</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <div class="text-slate-500 mb-1">Loại hẹn</div>
                <div class="font-medium">{{ ucfirst($appointment->appointment_type ?? '—') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Trạng thái</div>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    {{ $appointment->status === 'booked' ? 'bg-blue-100 text-blue-700' :
                       ($appointment->status === 'fulfilled' ? 'bg-green-100 text-green-700' :
                       ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600')) }}">
                    {{ ucfirst($appointment->status) }}
                </span>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Bắt đầu</div>
                <div class="font-medium">{{ $appointment->start_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Kết thúc</div>
                <div class="font-medium">{{ $appointment->end_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Thời lượng</div>
                <div class="font-medium">{{ $appointment->duration_minutes ? $appointment->duration_minutes . ' phút' : '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Lý do</div>
                <div class="font-medium">{{ $appointment->reason ?? '—' }}</div>
            </div>
        </div>
        @if($appointment->status === 'cancelled' && $appointment->cancel_reason)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="text-red-500 text-sm mb-1">Lý do huỷ</div>
                <div class="text-sm">{{ $appointment->cancel_reason }}</div>
            </div>
        @endif
    </div>

    @if($appointment->status === 'booked')
        <form method="POST" action="{{ route('appointments.destroy', $appointment->id) }}"
              onsubmit="return confirm('Huỷ lịch hẹn này?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600 border border-red-300 px-4 py-2 rounded-lg text-sm hover:bg-red-50">
                Huỷ lịch hẹn
            </button>
        </form>
    @endif
</div>
@endsection
