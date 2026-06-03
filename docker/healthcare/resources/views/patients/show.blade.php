@extends('layouts.app')
@section('page-title', 'Hồ sơ bệnh nhân')

@section('content')
<div class="p-6" style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('patients.index') }}" class="text-secondary text-decoration-none small">← Danh sách</a>
            <h1 class="h4 fw-bold text-dark mb-0">Hồ sơ bệnh nhân</h1>
        </div>
        <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-warning text-white">
            Chỉnh sửa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $patient->currentDemographics; @endphp

    {{-- Patient Banner --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="h4 fw-bold text-dark mb-2">
                        {{ $demo?->family_name }} {{ $demo?->given_name }}
                    </h2>
                    <div class="d-flex flex-wrap gap-3 text-secondary small">
                        <span><span class="fw-medium text-dark">MRN:</span> {{ $patient->mrn }}</span>
                        <span><span class="fw-medium text-dark">Ngày sinh:</span>
                            {{ $demo?->birth_date ? \Carbon\Carbon::parse($demo->birth_date)->format('d/m/Y') : '—' }}
                        </span>
                        <span><span class="fw-medium text-dark">Giới tính:</span>
                            {{ match($demo?->gender) {
                                'male'    => 'Nam',
                                'female'  => 'Nữ',
                                'other'   => 'Khác',
                                'unknown' => 'Không xác định',
                                default   => '—',
                            } }}
                        </span>
                        @if($demo?->phone)
                            <span><span class="fw-medium text-dark">ĐT:</span> {{ $demo->phone }}</span>
                        @endif
                        @if($demo?->email)
                            <span><span class="fw-medium text-dark">Email:</span> {{ $demo->email }}</span>
                        @endif
                    </div>
                </div>
                <span class="badge rounded-pill {{ $patient->status === 'active' ? 'bg-success' : 'bg-secondary' }} fs-6">
                    {{ ucfirst($patient->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Allergies --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-danger mb-3">⚠ Dị ứng</h6>
                    @forelse($patient->allergies as $allergy)
                        <div class="py-2 border-bottom">
                            <div class="fw-medium small">{{ $allergy->substance_name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ ucfirst($allergy->reaction_severity ?? 'unknown') }}</div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Không có dị ứng đã ghi nhận.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Conditions --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-secondary mb-3">Bệnh lý hiện tại</h6>
                    @forelse($patient->conditions as $condition)
                        <div class="py-2 border-bottom">
                            <div class="fw-medium small">{{ $condition->icd10_display ?? $condition->free_text }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $condition->icd10_code }}</div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Chưa có bệnh lý được ghi nhận.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-secondary mb-3">Tổng quan</h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Số lần khám</span>
                            <span class="fw-semibold">{{ $patient->encounters->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Dị ứng</span>
                            <span class="fw-semibold {{ $patient->allergies->count() > 0 ? 'text-danger' : '' }}">
                                {{ $patient->allergies->count() }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Bệnh lý</span>
                            <span class="fw-semibold">{{ $patient->conditions->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Encounter Timeline --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold text-dark mb-0">Lịch sử khám</h6>
                <a href="{{ route('encounters.create') }}?patient_id={{ $patient->id }}"
                   class="btn btn-sm btn-outline-primary">+ Tạo lần khám mới</a>
            </div>
            @forelse($patient->encounters as $encounter)
                <div class="d-flex align-items-start gap-3 py-3 border-bottom">
                    <div class="rounded-circle mt-1 flex-shrink-0 {{ $encounter->status === 'in-progress' ? 'bg-success' : 'bg-secondary' }}"
                         style="width:8px;height:8px;"></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-medium small">{{ $encounter->reason_text ?? 'Không rõ lý do' }}</span>
                                <span class="badge ms-2 {{ $encounter->encounter_type === 'emergency' ? 'bg-danger' : 'bg-primary' }}">
                                    {{ ucfirst($encounter->encounter_type) }}
                                </span>
                            </div>
                            <a href="{{ route('encounters.show', $encounter->id) }}"
                               class="text-primary text-decoration-none" style="font-size:0.75rem;">Xem chi tiết →</a>
                        </div>
                        <div class="text-muted mt-1" style="font-size:0.75rem;">
                            {{ $encounter->period_start?->format('d/m/Y H:i') }}
                            @if($encounter->period_end)
                                → {{ $encounter->period_end->format('d/m/Y H:i') }}
                            @else
                                <span class="text-success">(Đang điều trị)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted small py-3 mb-0">Chưa có lần khám nào.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
