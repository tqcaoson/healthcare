@extends('layouts.app')
@section('page-title', 'Chỉnh sửa lịch hẹn')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('appointments.show', $appointment->id) }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Chi tiết
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Chỉnh sửa lịch hẹn</h1>
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
    @php $demo = $appointment->patient?->currentDemographics; @endphp
    <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:32px;height:32px;background:rgba(139,92,246,0.1);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="color:#7c3aed;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-muted small mb-0">Bệnh nhân</p>
            <p class="fw-semibold text-dark mb-0">
                {{ $demo?->family_name }} {{ $demo?->given_name }}
                <span class="font-monospace text-muted small ms-1">({{ $appointment->patient?->mrn }})</span>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('appointments.update', $appointment->id) }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f5f3ff,#faf5ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2" style="width:32px;height:32px;background:rgba(139,92,246,0.1);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="color:#7c3aed;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin lịch hẹn</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach([
                                'booked'     => 'Đã đặt',
                                'arrived'    => 'Đã đến',
                                'fulfilled'  => 'Hoàn thành',
                                'cancelled'  => 'Đã huỷ',
                                'noshow'     => 'Không đến',
                                'checked-in' => 'Đã check-in',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $appointment->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Loại hẹn</label>
                        <select name="appointment_type" class="form-select">
                            @foreach(['routine'=>'Định kỳ','followup'=>'Tái khám','emergency'=>'Cấp cứu','checkup'=>'Khám tổng quát'] as $val => $label)
                                <option value="{{ $val }}" {{ old('appointment_type', $appointment->appointment_type) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at"
                               value="{{ old('start_at', $appointment->start_at?->format('Y-m-d\TH:i')) }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at"
                               value="{{ old('end_at', $appointment->end_at?->format('Y-m-d\TH:i')) }}"
                               class="form-control" required>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-medium">Lý do</label>
                    <input type="text" name="reason" value="{{ old('reason', $appointment->reason) }}"
                           class="form-control">
                </div>

                <div>
                    <label class="form-label fw-medium">Lý do huỷ</label>
                    <input type="text" name="cancel_reason" value="{{ old('cancel_reason', $appointment->cancel_reason) }}"
                           class="form-control"
                           placeholder="Điền nếu trạng thái là Đã huỷ">
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-outline-secondary">Huỷ</a>
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
