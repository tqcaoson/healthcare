@extends('layouts.app')
@section('page-title', 'Chi tiết thuốc')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('medications.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Chi tiết thuốc đã dùng</h1>
    </div>

    @php $demo = $administration->patient?->currentDemographics; @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <div class="font-bold text-blue-900">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="text-sm text-blue-700 mt-1">MRN: {{ $administration->patient?->mrn }}</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <div class="text-slate-500 mb-1">Tên thuốc</div>
                <div class="font-bold text-lg">{{ $administration->drug_name }}</div>
                @if($administration->rxnorm_code)
                    <div class="text-xs text-slate-500 font-mono">RxNorm: {{ $administration->rxnorm_code }}</div>
                @endif
            </div>
            <div>
                <div class="text-slate-500 mb-1">Liều dùng</div>
                <div class="font-bold text-xl">{{ $administration->dose_value }} {{ $administration->dose_unit }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Đường dùng</div>
                <div class="font-medium">{{ strtoupper($administration->route) }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Thời gian</div>
                <div class="font-medium">{{ $administration->administered_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Trạng thái</div>
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    {{ ucfirst($administration->status) }}
                </span>
                @if($administration->is_prn)
                    <span class="ml-1 px-2 py-1 rounded text-xs bg-purple-100 text-purple-700">PRN</span>
                @endif
            </div>
            <div>
                <div class="text-slate-500 mb-1">Xác minh barcode</div>
                <div>
                    <span class="{{ $administration->patient_verified_by_barcode ? 'text-green-600' : 'text-slate-400' }}">
                        {{ $administration->patient_verified_by_barcode ? '✓ Bệnh nhân' : '✗ Bệnh nhân' }}
                    </span>
                    ·
                    <span class="{{ $administration->medication_verified_by_barcode ? 'text-green-600' : 'text-slate-400' }}">
                        {{ $administration->medication_verified_by_barcode ? '✓ Thuốc' : '✗ Thuốc' }}
                    </span>
                </div>
            </div>
        </div>
        @if($administration->notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="text-slate-500 text-sm mb-1">Ghi chú</div>
                <div class="text-sm">{{ $administration->notes }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
