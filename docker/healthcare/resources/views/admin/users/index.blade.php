@extends('layouts.app')
@section('page-title', 'Quản lý người dùng')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Quản lý người dùng</h1>
            <p class="text-sm text-slate-500 mt-1">Tài khoản hệ thống</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Thêm người dùng
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm username, email, mã NV..."
                   class="flex-1 min-w-[200px] border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="role" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Tất cả vai trò --</option>
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Đã khóa</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
            </select>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Lọc
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}"
                   class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
                    Xóa lọc
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-sm text-slate-500">
            Tổng: <strong>{{ $users->total() }}</strong> người dùng
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Username</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Mã NV</th>
                    <th class="px-5 py-3 text-left">Vai trò</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Đăng nhập cuối</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $user->username }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $user->employee_id ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                {{ $roles[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColor = match($user->status) {
                                    'active'    => 'bg-green-100 text-green-700',
                                    'suspended' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-slate-100 text-slate-500',
                                };
                                $statusLabel = match($user->status) {
                                    'active'    => 'Hoạt động',
                                    'suspended' => 'Đã khóa',
                                    default     => 'Vô hiệu',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">
                            {{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-5 py-3 flex items-center gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-blue-600 hover:underline text-xs">Sửa</a>

                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Vô hiệu hóa người dùng này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs">Vô hiệu</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            Không có người dùng nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection