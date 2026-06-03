@extends('layouts.app')
@section('page-title', 'Chi tiết kết quả')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('results.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
            <h1 class="text-2xl font-bold text-slate-800">Chi tiết kết quả</h1>
        </div>
        <a href="{{ route('results.edit', $result->id) }}"
           class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Chỉnh sửa
        </a>
    </div>

    @if($result->is_critical)
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg font-semibold">
            ⚠ KẾT QUẢ NGUY KỊCH — Cần xử lý ngay!
        </div>
    @endif

    @php $demo = $result->patient?->currentDemographics; @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <div class="font-bold text-blue-900">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="text-sm text-blue-700 mt-1">MRN: {{ $result->patient?->mrn }}</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <div class="text-slate-500 mb-1">Tên xét nghiệm</div>
                <div class="font-bold text-base">{{ $result->loinc_display }}</div>
                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $result->loinc_code }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Kết quả</div>
                @if($result->value_quantity !== null)
                    <div class="font-bold text-2xl {{ in_array($result->abnormal_flag, ['H','HH']) ? 'text-red-700' : (in_array($result->abnormal_flag, ['L','LL']) ? 'text-blue-700' : 'text-slate-800') }}">
                        {{ $result->value_quantity }}
                        <span class="text-sm font-normal text-slate-500">{{ $result->value_unit }}</span>
                    </div>
                @else
                    <div class="font-medium">{{ $result->value_string ?? '—' }}</div>
                @endif
            </div>
            <div>
                <div class="text-slate-500 mb-1">Khoảng tham chiếu</div>
                <div class="font-medium">
                    @if($result->reference_low !== null || $result->reference_high !== null)
                        {{ $result->reference_low }} – {{ $result->reference_high }} {{ $result->value_unit }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Cờ</div>
                @php
                    $flagColors = ['HH'=>'bg-red-200 text-red-900','H'=>'bg-red-100 text-red-700',
                                   'LL'=>'bg-blue-200 text-blue-900','L'=>'bg-blue-100 text-blue-700',
                                   'N'=>'bg-green-100 text-green-700'];
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $flagColors[$result->abnormal_flag] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $result->abnormal_flag === 'null' ? 'N/A' : $result->abnormal_flag }}
                </span>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Thời gian</div>
                <div class="font-medium">{{ $result->resulted_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Trạng thái</div>
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                    {{ ucfirst($result->status) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
