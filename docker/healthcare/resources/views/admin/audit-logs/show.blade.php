@extends('layouts.app')
@section('page-title', 'Chi tiết Audit Log')

@section('content')
<div class="p-6 space-y-6 max-w-4xl">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.audit-logs.index') }}"
           class="text-slate-500 hover:text-slate-700 text-sm flex items-center gap-1">
            ← Quay lại
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Chi tiết Audit Log #{{ $auditLog->id }}</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Thời gian</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->event_at?->format('d/m/Y H:i:s') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Người dùng</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->user_name ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ $auditLog->user_role }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Loại sự kiện</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->event_type ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Hành động</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->action ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Tài nguyên</p>
                <p class="mt-1 font-medium text-slate-800">
                    {{ $auditLog->resource_type ?? '—' }}
                    @if($auditLog->resource_id) #{{ $auditLog->resource_id }} @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Bệnh nhân</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->patient_id ? '#'.$auditLog->patient_id : '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">IP</p>
                <p class="mt-1 font-mono text-sm text-slate-800">{{ $auditLog->ip_address ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Phương thức</p>
                <p class="mt-1 font-medium text-slate-800">{{ $auditLog->request_method ?? '—' }} {{ $auditLog->request_path ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Session</p>
                <p class="mt-1 font-mono text-xs text-slate-500 truncate">{{ $auditLog->session_id ?? '—' }}</p>
            </div>
        </div>

        @if($auditLog->old_values || $auditLog->new_values)
            <div class="border-t border-slate-100 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($auditLog->old_values)
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Giá trị cũ</p>
                        <pre class="bg-red-50 text-red-800 rounded-lg p-3 text-xs overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
                @if($auditLog->new_values)
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Giá trị mới</p>
                        <pre class="bg-green-50 text-green-800 rounded-lg p-3 text-xs overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        @endif

        @if($auditLog->user_agent)
            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">User Agent</p>
                <p class="text-xs text-slate-600 break-all">{{ $auditLog->user_agent }}</p>
            </div>
        @endif

        @if($auditLog->log_hash)
            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Log Hash</p>
                <p class="font-mono text-xs text-slate-500">{{ $auditLog->log_hash }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
