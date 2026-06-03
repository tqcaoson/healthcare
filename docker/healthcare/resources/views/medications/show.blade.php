@extends('layouts.app')
@section('page-title', 'Chi tiết thuốc')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('medications.index') }}" class="text-secondary text-decoration-none small">← Danh sách</a>
        <h1 class="h4 fw-bold text-dark mb-0">Chi tiết thuốc đã dùng</h1>
    </div>

    @php $demo = $administration->patient?->currentDemographics; @endphp

    {{-- Patient banner --}}
    <div class="alert alert-primary mb-0" role="alert">
        <div class="fw-bold">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="small mt-1">MRN: {{ $administration->patient?->mrn }}</div>
    </div>

    {{-- Detail card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Tên thuốc</p>
                    <p class="fw-bold fs-5 mb-0">{{ $administration->drug_name }}</p>
                    @if($administration->rxnorm_code)
                        <p class="font-monospace text-muted mb-0" style="font-size:0.75rem;">RxNorm: {{ $administration->rxnorm_code }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Liều dùng</p>
                    <p class="fw-bold fs-4 mb-0">{{ $administration->dose_value }} {{ $administration->dose_unit }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Đường dùng</p>
                    <p class="fw-medium mb-0">{{ strtoupper($administration->route) }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Thời gian</p>
                    <p class="fw-medium mb-0">{{ $administration->administered_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Trạng thái</p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success">{{ ucfirst($administration->status) }}</span>
                        @if($administration->is_prn)
                            <span class="badge rounded-pill bg-purple" style="background-color:#7c3aed!important;">PRN</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Xác minh barcode</p>
                    <div class="d-flex gap-3 small">
                        <span class="{{ $administration->patient_verified_by_barcode ? 'text-success' : 'text-secondary' }}">
                            {{ $administration->patient_verified_by_barcode ? '✓' : '✗' }} Bệnh nhân
                        </span>
                        <span class="{{ $administration->medication_verified_by_barcode ? 'text-success' : 'text-secondary' }}">
                            {{ $administration->medication_verified_by_barcode ? '✓' : '✗' }} Thuốc
                        </span>
                    </div>
                </div>
            </div>

            @if($administration->notes)
                <hr class="my-3">
                <p class="text-muted small mb-1">Ghi chú</p>
                <p class="small mb-0">{{ $administration->notes }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
