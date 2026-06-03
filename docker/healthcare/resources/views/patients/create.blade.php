@extends('layouts.app')
@section('page-title', 'Thêm bệnh nhân mới')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('patients.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Thêm bệnh nhân mới</h1>
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

    <form method="POST" action="{{ route('patients.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

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
                        <input type="text" name="family_name" value="{{ old('family_name') }}"
                               class="form-control @error('family_name') is-invalid @enderror"
                               placeholder="Nguyễn" required>
                        @error('family_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tên <span class="text-danger">*</span></label>
                        <input type="text" name="given_name" value="{{ old('given_name') }}"
                               class="form-control @error('given_name') is-invalid @enderror"
                               placeholder="Văn An" required>
                        @error('given_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Ngày sinh <span class="text-danger">*</span></label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}"
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
                            <option value="">-- Chọn giới tính --</option>
                            <option value="male"    {{ old('gender') === 'male'    ? 'selected' : '' }}>Nam</option>
                            <option value="female"  {{ old('gender') === 'female'  ? 'selected' : '' }}>Nữ</option>
                            <option value="other"   {{ old('gender') === 'other'   ? 'selected' : '' }}>Khác</option>
                            <option value="unknown" {{ old('gender') === 'unknown' ? 'selected' : '' }}>Không xác định</option>
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
                            <input type="text" name="phone" value="{{ old('phone') }}"
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
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="form-control" placeholder="example@email.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu bệnh nhân
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
