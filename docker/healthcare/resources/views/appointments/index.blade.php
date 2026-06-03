@extends('layouts.app')
@section('page-title', 'Lịch hẹn')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Lịch hẹn (Scheduling)</h1>
        <a href="{{ route('appointments.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Đặt lịch hẹn
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('appointments.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <select name="status"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                @foreach(['booked','arrived','fulfilled','cancelled','noshow'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800">Lọc</button>
            @if(request()->hasAny(['status','date']))
                <a href="{{ route('appointments.index') }}" class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">Xoá lọc</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Bệnh nhân</th>
                    <th class="px-5 py-3 text-left">Loại</th>
                    <th class="px-5 py-3 text-left">Lý do</th>
                    <th class="px-5 py-3 text-left">Bắt đầu</th>
                    <th class="px-5 py-3 text-left">Thời lượng</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($appointments as $appt)
                    @php $demo = $appt->patient?->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $appt->patient?->mrn }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ ucfirst($appt->appointment_type ?? '—') }}</td>
                        <td class="px-5 py-3 text-slate-600 max-w-xs truncate">{{ $appt->reason ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $appt->start_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $appt->duration_minutes ? $appt->duration_minutes . ' phút' : '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $appt->status === 'booked' ? 'bg-blue-100 text-blue-700' :
                                   ($appt->status === 'fulfilled' ? 'bg-green-100 text-green-700' :
                                   ($appt->status === 'cancelled' ? 'bg-red-100 text-red-600' :
                                   ($appt->status === 'noshow' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600'))) }}">
                                {{ ucfirst($appt->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('appointments.show', $appt->id) }}" class="text-blue-600 hover:underline text-xs">Xem</a>
                                <a href="{{ route('appointments.edit', $appt->id) }}" class="text-amber-600 hover:underline text-xs">Sửa</a>
                                <form method="POST" action="{{ route('appointments.destroy', $appt->id) }}"
                                      onsubmit="return confirm('Huỷ lịch hẹn này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-xs">Huỷ</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            Chưa có lịch hẹn. <a href="{{ route('appointments.create') }}" class="text-blue-600 hover:underline">Đặt ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($appointments->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $appointments->links() }}</div>
        @endif
    </div>
</div>
@endsection
