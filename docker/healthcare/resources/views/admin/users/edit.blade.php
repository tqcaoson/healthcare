@extends('layouts.app')
@section('page-title', 'Sửa người dùng')

@section('content')
<div class="p-6 max-w-2xl">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users.index') }}"
           class="text-slate-500 hover:text-slate-700 text-sm">← Quay lại</a>
        <h1 class="text-2xl font-bold text-slate-800">Sửa người dùng: {{ $user->username }}</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu mới</label>
                <input type="password" name="password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-slate-400 mt-1">Để trống nếu không đổi mật khẩu.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Vai trò <span class="text-red-500">*</span></label>
                <select name="role" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mã nhân viên</label>
                <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">NPI (bác sĩ)</label>
                <input type="text" name="provider_npi" value="{{ old('provider_npi', $user->provider_npi) }}" maxlength="10"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Đã khóa</option>
                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                </select>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4 text-xs text-slate-400 space-y-1">
            <p>UUID: <span class="font-mono">{{ $user->uuid }}</span></p>
            <p>Đăng nhập cuối: {{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}</p>
            @if($user->locked_until)
                <p class="text-yellow-600">Tài khoản bị khóa đến: {{ $user->locked_until->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Lưu thay đổi
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">
                Hủy
            </a>
        </div>
    </form>
</div>
@endsection
