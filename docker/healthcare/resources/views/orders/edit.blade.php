@extends('layouts.app')
@section('page-title', 'Chỉnh sửa lệnh')

@section('content')
<div class="p-6 max-w-3xl mx-auto" style="display:flex;flex-direction:column;gap:1.5rem;">

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('orders.show', $order->id) }}"
           class="d-flex align-items-center gap-1 text-secondary text-decoration-none small">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Chi tiết
        </a>
        <span class="text-secondary">/</span>
        <h1 class="h5 fw-bold text-dark mb-0">Chỉnh sửa lệnh</h1>
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
    <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-4 py-3">
        <div class="d-flex align-items-center justify-content-center rounded-2 bg-secondary bg-opacity-10 flex-shrink-0" style="width:32px;height:32px;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-secondary">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-muted small mb-0">Số lệnh</p>
            <p class="fw-bold font-monospace text-dark mb-0">{{ $order->order_number }}</p>
        </div>
        <span class="ms-auto badge rounded-pill {{ in_array($order->status, ['active','pending']) ? 'bg-primary' : 'bg-secondary' }}">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    <form method="POST" action="{{ route('orders.update', $order->id) }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 bg-secondary bg-opacity-10" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" class="text-secondary">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h6 class="mb-0 fw-semibold text-secondary">Cập nhật lệnh</h6>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach([
                                'draft'     => 'Draft',
                                'pending'   => 'Pending',
                                'active'    => 'Active',
                                'on-hold'   => 'On Hold',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'stopped'   => 'Stopped',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $order->status) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Ưu tiên <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach(['routine'=>'Routine','urgent'=>'Urgent','stat'=>'STAT','asap'=>'ASAP'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $order->priority) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-medium">Chỉ định lâm sàng</label>
                    <textarea name="clinical_indication" rows="4"
                              class="form-control"
                              placeholder="Nhập chỉ định lâm sàng...">{{ old('clinical_indication', $order->clinical_indication) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc</p>
            <div class="d-flex gap-2">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary">Huỷ</a>
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
