@extends('layouts.app')
@section('page-title', 'Đặt lệnh mới')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('orders.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Danh sách
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Đặt lệnh mới (CPOE)</h1>
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

    <form method="POST" action="{{ route('orders.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Lần khám --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f0fdfa,#ecfeff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-success">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Lần khám</h6>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label fw-medium">Chọn lần khám <span class="text-danger">*</span></label>
                <select name="encounter_id" class="form-select" required>
                    <option value="">-- Chọn lần khám đang điều trị --</option>
                    @foreach($encounters as $enc)
                        @php $d = $enc->patient?->currentDemographics; @endphp
                        <option value="{{ $enc->id }}"
                            {{ old('encounter_id', request('encounter_id')) == $enc->id ? 'selected' : '' }}>
                            {{ $d?->family_name }} {{ $d?->given_name }} — {{ $enc->period_start?->format('d/m/Y') }}
                            ({{ ucfirst($enc->encounter_type) }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Chi tiết lệnh --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-secondary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-secondary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Chi tiết lệnh</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Loại lệnh <span class="text-danger">*</span></label>
                        <select name="order_type" class="form-select" required>
                            <option value="">-- Chọn loại --</option>
                            @foreach([
                                'lab'        => 'Xét nghiệm (Lab)',
                                'radiology'  => 'Hình ảnh (Radiology)',
                                'medication' => 'Thuốc (Medication)',
                                'nursing'    => 'Điều dưỡng (Nursing)',
                                'diet'       => 'Chế độ ăn (Diet)',
                                'referral'   => 'Chuyển khoa (Referral)',
                                'procedure'  => 'Thủ thuật (Procedure)',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('order_type') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Mức ưu tiên <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="routine" {{ old('priority', 'routine') === 'routine' ? 'selected' : '' }}>Routine</option>
                            <option value="urgent"  {{ old('priority') === 'urgent'  ? 'selected' : '' }}>Urgent</option>
                            <option value="stat"    {{ old('priority') === 'stat'    ? 'selected' : '' }}>STAT</option>
                            <option value="asap"    {{ old('priority') === 'asap'    ? 'selected' : '' }}>ASAP</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-medium">Chỉ định lâm sàng</label>
                    <textarea name="clinical_indication" rows="3"
                              class="form-control"
                              placeholder="Mô tả lý do lâm sàng...">{{ old('clinical_indication') }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Đặt lệnh
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
