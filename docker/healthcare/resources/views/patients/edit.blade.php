@extends('layouts.app')
@section('page-title', 'Chỉnh sửa bệnh nhân')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('patients.show', $patient->id) }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Hồ sơ bệnh nhân
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Chỉnh sửa thông tin</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
            <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20" class="flex-shrink-0 mt-1">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
            </svg>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $demo = $patient->currentDemographics; @endphp

    {{-- MRN Badge --}}
    <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-center rounded-2 bg-secondary bg-opacity-10 flex-shrink-0" style="width:32px;height:32px;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-secondary">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
        </div>
        <div>
            <p class="text-muted small mb-0">Mã bệnh nhân (MRN)</p>
            <p class="fw-bold font-monospace text-dark mb-0">{{ $patient->mrn }}</p>
        </div>
        <span class="ms-auto badge rounded-pill {{ $patient->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
            {{ $patient->status === 'active' ? 'Đang hoạt động' : ucfirst($patient->status) }}
        </span>
    </div>

    <form method="POST" action="{{ route('patients.update', $patient->id) }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf @method('PUT')

        {{-- Thông tin cá nhân --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#eff6ff,#eef2ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin cá nhân</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Họ <span class="text-danger">*</span></label>
                        <input type="text" name="family_name"
                               value="{{ old('family_name', $demo?->family_name) }}"
                               class="form-control @error('family_name') is-invalid @enderror"
                               required>
                        @error('family_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tên <span class="text-danger">*</span></label>
                        <input type="text" name="given_name"
                               value="{{ old('given_name', $demo?->given_name) }}"
                               class="form-control @error('given_name') is-invalid @enderror"
                               required>
                        @error('given_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Ngày sinh <span class="text-danger">*</span></label>
                        <input type="date" name="birth_date"
                               value="{{ old('birth_date', $demo?->birth_date) }}"
                               class="form-control @error('birth_date') is-invalid @enderror"
                               required>
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Giới tính <span class="text-danger">*</span></label>
                        <select name="gender"
                                class="form-select @error('gender') is-invalid @enderror"
                                required>
                            <option value="male"    {{ old('gender', $demo?->gender) === 'male'    ? 'selected' : '' }}>Nam</option>
                            <option value="female"  {{ old('gender', $demo?->gender) === 'female'  ? 'selected' : '' }}>Nữ</option>
                            <option value="other"   {{ old('gender', $demo?->gender) === 'other'   ? 'selected' : '' }}>Khác</option>
                            <option value="unknown" {{ old('gender', $demo?->gender) === 'unknown' ? 'selected' : '' }}>Không xác định</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Liên hệ --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f0fdf4,#ecfdf5);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-success">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin liên hệ</h6>
                    <span class="text-muted small">(tuỳ chọn)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $demo?->phone) }}"
                                   class="form-control" placeholder="0901 234 567">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" name="email"
                                   value="{{ old('email', $demo?->email) }}"
                                   class="form-control" placeholder="example@email.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lưu ý phiên bản --}}
        <div class="alert alert-warning d-flex gap-2 align-items-start" role="alert">
            <svg fill="currentColor" viewBox="0 0 20 20" width="16" height="16" class="flex-shrink-0 mt-1">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <p class="mb-0 small">Cập nhật thông tin sẽ tạo một phiên bản mới. Lịch sử thay đổi được lưu lại đầy đủ.</p>
        </div>

        {{-- Actions --}}
        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-secondary">Huỷ</a>
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
