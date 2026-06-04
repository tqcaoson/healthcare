@extends('layouts.app')
@section('page-title', 'Audit Log')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Audit Log</h1>
            <p class="text-sm text-slate-500 mt-1">Nhật ký hoạt động hệ thống</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm user, action, IP..."
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="event_type" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Tất cả loại sự kiện --</option>
                @foreach($eventTypes as $type)
                    <option value="{{ $type }}" {{ request('event_type') === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Lọc
            </button>
            @if(request()->hasAny(['search', 'event_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
                    Xóa lọc
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-sm text-slate-500">
            Tổng: <strong>{{ $logs->total() }}</strong> bản ghi
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Thời gian</th>
                        <th class="px-4 py-3 text-left">Người dùng</th>
                        <th class="px-4 py-3 text-left">Loại sự kiện</th>
                        <th class="px-4 py-3 text-left">Hành động</th>
                        <th class="px-4 py-3 text-left">Tài nguyên</th>
                        <th class="px-4 py-3 text-left">IP</th>
                        <th class="px-4 py-3 text-left">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap font-mono text-xs">
                                {{ $log->event_at?->format('d/m/Y H:i:s') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $log->user_name ?? '—' }}</div>
                                <div class="text-xs text-slate-400">{{ $log->user_role }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeColor = match(true) {
                                        str_contains($log->event_type ?? '', 'delete') => 'bg-red-100 text-red-700',
                                        str_contains($log->event_type ?? '', 'create') => 'bg-green-100 text-green-700',
                                        str_contains($log->event_type ?? '', 'update') => 'bg-yellow-100 text-yellow-700',
                                        str_contains($log->event_type ?? '', 'login')  => 'bg-blue-100 text-blue-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                    {{ $log->event_type ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->action ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $log->resource_type ?? '—' }}
                                @if($log->resource_id)
                                    <span class="text-slate-400 text-xs">#{{ $log->resource_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="text-blue-600 hover:underline text-xs">Xem</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                Không có bản ghi nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
