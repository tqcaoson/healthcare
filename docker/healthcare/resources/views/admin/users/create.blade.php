@extends('layouts.app')
@section('page-title', 'Thêm người dùng')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.users.index') }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Thêm người dùng</h1>
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

    <form method="POST" action="{{ route('admin.users.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Thông tin đăng nhập --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#eff6ff,#eef2ff);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Thông tin đăng nhập</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" value="{{ old('username') }}" required
                               class="form-control @error('username') is-invalid @enderror"
                               placeholder="bsnguyenvana">
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="example@hospital.vn">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" required
                               class="form-control @error('password') is-invalid @enderror">
                        <div class="form-text">Tối thiểu 8 ký tự, có chữ hoa, chữ thường và số.</div>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Vai trò & phân quyền --}}
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom" style="background:linear-gradient(to right,#f0fdf4,#ecfdf5);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-success">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Vai trò & phân quyền</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Vai trò <span class="text-danger">*</span></label>
                        <select name="role" required class="form-select @error('role') is-invalid @enderror">
                            <option value="">-- Chọn vai trò --</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" required class="form-select @error('status') is-invalid @enderror">
                            <option value="active"    {{ old('status', 'active') === 'active'    ? 'selected' : '' }}>Hoạt động</option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Đã khóa</option>
                            <option value="inactive"  {{ old('status') === 'inactive'  ? 'selected' : '' }}>Vô hiệu</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Mã nhân viên</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}"
                               class="form-control @error('employee_id') is-invalid @enderror"
                               placeholder="NV-001">
                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">NPI <span class="text-muted small">(bác sĩ)</span></label>
                        <input type="text" name="provider_npi" value="{{ old('provider_npi') }}" maxlength="10"
                               class="form-control @error('provider_npi') is-invalid @enderror"
                               placeholder="1234567890">
                        @error('provider_npi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tạo người dùng
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
