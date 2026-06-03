@extends('layouts.app')
@section('page-title', 'Chi tiết lịch hẹn')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('appointments.index') }}" class="text-secondary text-decoration-none small">← Danh sách</a>
            <h1 class="h4 fw-bold text-dark mb-0">Chi tiết lịch hẹn</h1>
        </div>
        <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-warning text-white">
            Chỉnh sửa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $appointment->patient?->currentDemographics; @endphp

    {{-- Patient banner --}}
    <div class="alert alert-primary mb-0" role="alert">
        <div class="fw-bold">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="small mt-1">MRN: {{ $appointment->patient?->mrn }}</div>
    </div>

    {{-- Detail card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Loại hẹn</p>
                    <p class="fw-medium mb-0">{{ ucfirst($appointment->appointment_type ?? '—') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Trạng thái</p>
                    <span class="badge rounded-pill fs-6
                        {{ $appointment->status === 'booked'     ? 'bg-primary'   :
                          ($appointment->status === 'fulfilled'  ? 'bg-success'   :
                          ($appointment->status === 'cancelled'  ? 'bg-danger'    : 'bg-secondary')) }}">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Bắt đầu</p>
                    <p class="fw-medium mb-0">{{ $appointment->start_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Kết thúc</p>
                    <p class="fw-medium mb-0">{{ $appointment->end_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Thời lượng</p>
                    <p class="fw-medium mb-0">{{ $appointment->duration_minutes ? $appointment->duration_minutes . ' phút' : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">Lý do</p>
                    <p class="fw-medium mb-0">{{ $appointment->reason ?? '—' }}</p>
                </div>
            </div>

            @if($appointment->status === 'cancelled' && $appointment->cancel_reason)
                <hr class="my-3">
                <p class="text-danger small mb-1">Lý do huỷ</p>
                <p class="small mb-0">{{ $appointment->cancel_reason }}</p>
            @endif
        </div>
    </div>

    @if($appointment->status === 'booked')
        <form method="POST" action="{{ route('appointments.destroy', $appointment->id) }}"
              onsubmit="return confirm('Huỷ lịch hẹn này?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Huỷ lịch hẹn</button>
        </form>
    @endif
</div>
@endsection
