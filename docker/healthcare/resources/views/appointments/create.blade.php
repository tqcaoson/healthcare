@extends('layouts.app')
@section('page-title', 'Đặt lịch hẹn')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('appointments.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Danh sách
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Đặt lịch hẹn mới</h1>
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

    <form method="POST" action="{{ route('appointments.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
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

        {{-- Thông tin lịch hẹn --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f5f3ff,#faf5ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2" style="width:32px;height:32px;background:rgba(139,92,246,0.1);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="color:#7c3aed;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin lịch hẹn</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Loại hẹn</label>
                        <select name="appointment_type" class="form-select">
                            <option value="routine"   {{ old('appointment_type', 'routine') === 'routine'   ? 'selected' : '' }}>Định kỳ</option>
                            <option value="followup"  {{ old('appointment_type') === 'followup'  ? 'selected' : '' }}>Tái khám</option>
                            <option value="emergency" {{ old('appointment_type') === 'emergency' ? 'selected' : '' }}>Cấp cứu</option>
                            <option value="checkup"   {{ old('appointment_type') === 'checkup'   ? 'selected' : '' }}>Khám tổng quát</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Lý do</label>
                        <input type="text" name="reason" value="{{ old('reason') }}"
                               class="form-control" placeholder="vd: Kiểm tra huyết áp">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at') }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at') }}"
                               class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Đặt lịch
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
