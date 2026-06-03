@extends('layouts.app')
@section('page-title', 'Danh sách lần khám')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Lần khám (Encounters)</h1>
        <a href="{{ route('encounters.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Tạo lần khám
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('encounters.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <select name="status"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                @foreach(['planned','arrived','triaged','in-progress','finished','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="encounter_type"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả loại</option>
                @foreach(['inpatient','outpatient','emergency','telehealth'] as $t)
                    <option value="{{ $t }}" {{ request('encounter_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800">
                Lọc
            </button>
            @if(request()->hasAny(['status','encounter_type']))
                <a href="{{ route('encounters.index') }}"
                   class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
                    Xoá lọc
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Bệnh nhân</th>
                    <th class="px-5 py-3 text-left">Loại</th>
                    <th class="px-5 py-3 text-left">Lý do</th>
                    <th class="px-5 py-3 text-left">Bắt đầu</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($encounters as $encounter)
                    @php $demo = $encounter->patient?->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $encounter->patient?->mrn }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $encounter->encounter_type === 'emergency' ? 'bg-red-100 text-red-700' :
                                   ($encounter->encounter_type === 'inpatient' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst($encounter->encounter_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 max-w-xs truncate">
                            {{ $encounter->reason_text ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ $encounter->period_start?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $encounter->status === 'in-progress' ? 'bg-green-100 text-green-700' :
                                   ($encounter->status === 'finished' ? 'bg-slate-100 text-slate-600' :
                                   ($encounter->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700')) }}">
                                {{ ucfirst($encounter->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('encounters.show', $encounter->id) }}"
                                   class="text-blue-600 hover:underline text-xs">Xem</a>
                                <a href="{{ route('encounters.edit', $encounter->id) }}"
                                   class="text-amber-600 hover:underline text-xs">Sửa</a>
                                <form method="POST" action="{{ route('encounters.destroy', $encounter->id) }}"
                                      onsubmit="return confirm('Huỷ lần khám này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-xs">Huỷ</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                            Chưa có lần khám nào. <a href="{{ route('encounters.create') }}" class="text-blue-600 hover:underline">Tạo ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($encounters->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $encounters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
