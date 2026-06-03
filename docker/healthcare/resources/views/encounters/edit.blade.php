@extends('layouts.app')
@section('page-title', 'Chỉnh sửa lần khám')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('encounters.show', $encounter->id) }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Chi tiết
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Chỉnh sửa lần khám</h1>
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
    @php $demo = $encounter->patient?->currentDemographics; @endphp
    <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10 flex-shrink-0" style="width:32px;height:32px;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-success">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-muted small mb-0">Bệnh nhân</p>
            <p class="fw-semibold text-dark mb-0">
                {{ $demo?->family_name }} {{ $demo?->given_name }}
                <span class="font-monospace text-muted small ms-1">({{ $encounter->patient?->mrn }})</span>
            </p>
        </div>
        <span class="ms-auto badge rounded-pill {{ $encounter->status === 'in-progress' ? 'bg-success' : 'bg-secondary' }}">
            {{ ucfirst($encounter->status) }}
        </span>
    </div>

    <form method="POST" action="{{ route('encounters.update', $encounter->id) }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f0fdfa,#ecfeff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-success">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Cập nhật lần khám</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach([
                                'planned'    => 'Đã lên kế hoạch',
                                'arrived'    => 'Đã đến',
                                'triaged'    => 'Đã phân loại',
                                'in-progress'=> 'Đang khám',
                                'onleave'    => 'Tạm vắng',
                                'finished'   => 'Hoàn thành',
                                'cancelled'  => 'Đã huỷ',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $encounter->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Thời gian kết thúc</label>
                        <input type="datetime-local" name="period_end"
                               value="{{ old('period_end', $encounter->period_end?->format('Y-m-d\TH:i')) }}"
                               class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-label fw-medium">Lý do khám</label>
                    <textarea name="reason_text" rows="3"
                              class="form-control">{{ old('reason_text', $encounter->reason_text) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('encounters.show', $encounter->id) }}" class="btn btn-outline-secondary">Huỷ</a>
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
