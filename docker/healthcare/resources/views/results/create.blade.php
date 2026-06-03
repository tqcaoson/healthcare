@extends('layouts.app')
@section('page-title', 'Nhập kết quả xét nghiệm')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('results.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Danh sách
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Nhập kết quả xét nghiệm</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
            <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20" class="flex-shrink-0 mt-1">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
            </svg>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('results.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Bệnh nhân --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#eff6ff,#eef2ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Bệnh nhân</h6>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label fw-medium">Chọn bệnh nhân <span class="text-danger">*</span></label>
                <select name="patient_id" class="form-select" required>
                    <option value="">-- Chọn bệnh nhân --</option>
                    @foreach($patients as $patient)
                        @php $d = $patient->currentDemographics; @endphp
                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $d?->family_name }} {{ $d?->given_name }} ({{ $patient->mrn }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Thông tin xét nghiệm --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#ecfeff,#f0fdfa);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-info">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Kết quả xét nghiệm</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">LOINC Code <span class="text-danger">*</span></label>
                        <input type="text" name="loinc_code" value="{{ old('loinc_code') }}"
                               class="form-control font-monospace"
                               placeholder="vd: 2345-7" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tên xét nghiệm <span class="text-danger">*</span></label>
                        <input type="text" name="loinc_display" value="{{ old('loinc_display') }}"
                               class="form-control"
                               placeholder="vd: Glucose [Mass/volume] in Blood" required>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Giá trị số</label>
                        <input type="number" step="0.001" name="value_quantity" value="{{ old('value_quantity') }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Đơn vị</label>
                        <input type="text" name="value_unit" value="{{ old('value_unit') }}"
                               class="form-control" placeholder="vd: mg/dL, mmol/L">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Cờ bất thường <span class="text-danger">*</span></label>
                        <select name="abnormal_flag" class="form-select" required>
                            <option value="N"    {{ old('abnormal_flag', 'N') === 'N'    ? 'selected' : '' }}>N — Bình thường</option>
                            <option value="H"    {{ old('abnormal_flag') === 'H'    ? 'selected' : '' }}>H — Cao</option>
                            <option value="L"    {{ old('abnormal_flag') === 'L'    ? 'selected' : '' }}>L — Thấp</option>
                            <option value="HH"   {{ old('abnormal_flag') === 'HH'   ? 'selected' : '' }}>HH — Rất cao (Critical)</option>
                            <option value="LL"   {{ old('abnormal_flag') === 'LL'   ? 'selected' : '' }}>LL — Rất thấp (Critical)</option>
                            <option value="A"    {{ old('abnormal_flag') === 'A'    ? 'selected' : '' }}>A — Bất thường</option>
                            <option value="null" {{ old('abnormal_flag') === 'null' ? 'selected' : '' }}>N/A</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Thời gian có kết quả <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="resulted_at"
                               value="{{ old('resulted_at', now()->format('Y-m-d\TH:i')) }}"
                               class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('results.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu kết quả
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
