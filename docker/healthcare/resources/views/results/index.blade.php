@extends('layouts.app')
@section('page-title', 'Kết quả xét nghiệm')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Kết quả xét nghiệm (Lab Results)</h1>
        <a href="{{ route('results.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Nhập kết quả
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('results.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <select name="flag"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả kết quả</option>
                <option value="critical" {{ request('flag') === 'critical' ? 'selected' : '' }}>⚠ Nguy kịch (Critical)</option>
                <option value="abnormal" {{ request('flag') === 'abnormal' ? 'selected' : '' }}>Bất thường (Abnormal)</option>
            </select>
            <select name="days"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="1"  {{ request('days') == 1  ? 'selected' : '' }}>24 giờ qua</option>
                <option value="7"  {{ request('days', 7) == 7  ? 'selected' : '' }}>7 ngày qua</option>
                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>30 ngày qua</option>
                <option value="90" {{ request('days') == 90 ? 'selected' : '' }}>90 ngày qua</option>
            </select>
            <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800">Lọc</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Bệnh nhân</th>
                    <th class="px-5 py-3 text-left">Xét nghiệm</th>
                    <th class="px-5 py-3 text-right">Giá trị</th>
                    <th class="px-5 py-3 text-left">Khoảng tham chiếu</th>
                    <th class="px-5 py-3 text-left">Cờ</th>
                    <th class="px-5 py-3 text-left">Thời gian</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($results as $result)
                    @php $demo = $result->patient?->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50 {{ $result->is_critical ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $result->patient?->mrn }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $result->loinc_display }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $result->loinc_code }}</div>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($result->value_quantity !== null)
                                <span class="font-bold text-base {{ in_array($result->abnormal_flag, ['H','HH']) ? 'text-red-700' : (in_array($result->abnormal_flag, ['L','LL']) ? 'text-blue-700' : '') }}">
                                    {{ $result->value_quantity }}
                                </span>
                                <span class="text-slate-500 text-xs ml-1">{{ $result->value_unit }}</span>
                            @else
                                <span class="text-slate-600">{{ $result->value_string ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            @if($result->reference_low !== null || $result->reference_high !== null)
                                {{ $result->reference_low }} – {{ $result->reference_high }} {{ $result->value_unit }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $flagColors = [
                                    'HH' => 'bg-red-200 text-red-900',
                                    'H'  => 'bg-red-100 text-red-700',
                                    'LL' => 'bg-blue-200 text-blue-900',
                                    'L'  => 'bg-blue-100 text-blue-700',
                                    'A'  => 'bg-orange-100 text-orange-700',
                                    'N'  => 'bg-green-100 text-green-700',
                                ];
                                $flagColor = $flagColors[$result->abnormal_flag] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <div class="flex items-center gap-1">
                                @if($result->is_critical)
                                    <span class="text-red-600 font-bold text-xs">⚠</span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $flagColor }}">
                                    {{ $result->abnormal_flag === 'null' ? 'N/A' : $result->abnormal_flag }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            {{ $result->resulted_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                {{ ucfirst($result->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('results.show', $result->id) }}" class="text-blue-600 hover:underline text-xs">Xem</a>
                                <a href="{{ route('results.edit', $result->id) }}" class="text-amber-600 hover:underline text-xs">Sửa</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                            Chưa có kết quả xét nghiệm. <a href="{{ route('results.create') }}" class="text-blue-600 hover:underline">Nhập ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($results->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $results->links() }}</div>
        @endif
    </div>
</div>
@endsection
