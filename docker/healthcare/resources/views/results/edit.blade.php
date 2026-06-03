@extends('layouts.app')
@section('page-title', 'Sửa kết quả xét nghiệm')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('results.show', $result->id) }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Chi tiết
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Sửa kết quả xét nghiệm</h1>
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

    {{-- Context badge --}}
    @php $demo = $result->patient?->currentDemographics; @endphp
    <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10 flex-shrink-0" style="width:32px;height:32px;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-info">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-muted small mb-0">Bệnh nhân</p>
            <p class="fw-semibold text-dark mb-0 text-truncate">
                {{ $demo?->family_name }} {{ $demo?->given_name }}
                <span class="font-monospace text-muted small ms-1">({{ $result->patient?->mrn }})</span>
            </p>
        </div>
        <div class="ms-auto text-end flex-shrink-0">
            <p class="text-muted small mb-0">Xét nghiệm</p>
            <p class="fw-semibold text-dark mb-0 small">{{ $result->loinc_display }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('results.update', $result->id) }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#ecfeff,#f0fdfa);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-info">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Cập nhật kết quả</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Giá trị số</label>
                        <input type="number" step="0.001" name="value_quantity"
                               value="{{ old('value_quantity', $result->value_quantity) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Đơn vị</label>
                        <input type="text" name="value_unit" value="{{ old('value_unit', $result->value_unit) }}"
                               class="form-control" placeholder="vd: mg/dL, mmol/L">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Cờ bất thường <span class="text-danger">*</span></label>
                        <select name="abnormal_flag" class="form-select" required>
                            @foreach([
                                'N'    => 'N — Bình thường',
                                'H'    => 'H — Cao',
                                'L'    => 'L — Thấp',
                                'HH'   => 'HH — Rất cao (Critical)',
                                'LL'   => 'LL — Rất thấp (Critical)',
                                'A'    => 'A — Bất thường',
                                'null' => 'N/A',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('abnormal_flag', $result->abnormal_flag) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach([
                                'registered'  => 'Registered',
                                'preliminary' => 'Preliminary',
                                'final'       => 'Final',
                                'corrected'   => 'Corrected',
                                'amended'     => 'Amended',
                                'cancelled'   => 'Cancelled',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $result->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('results.show', $result->id) }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Cập nhật
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
