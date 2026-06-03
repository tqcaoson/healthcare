@extends('layouts.app')
@section('page-title', 'Chi tiết lệnh')

@section('content')
<div class="p-6 max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
            <h1 class="text-2xl font-bold text-slate-800">Chi tiết lệnh</h1>
        </div>
        @if(!in_array($order->status, ['cancelled','completed']))
            <a href="{{ route('orders.edit', $order->id) }}"
               class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Chỉnh sửa
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $order->patient?->currentDemographics; @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <div class="font-bold text-blue-900">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
        <div class="text-sm text-blue-700 mt-1">MRN: {{ $order->patient?->mrn }}</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <div class="text-slate-500 mb-1">Mã lệnh</div>
                <div class="font-mono font-bold">{{ $order->order_number }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Trạng thái</div>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    {{ $order->status === 'active' ? 'bg-green-100 text-green-700' :
                       ($order->status === 'completed' ? 'bg-blue-100 text-blue-700' :
                       ($order->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700')) }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Loại lệnh</div>
                <div class="font-medium">{{ ucfirst($order->order_type) }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Mức ưu tiên</div>
                <span class="px-2 py-1 rounded text-xs font-medium
                    {{ $order->priority === 'stat' ? 'bg-red-100 text-red-700' :
                       ($order->priority === 'urgent' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ strtoupper($order->priority) }}
                </span>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Đặt lúc</div>
                <div class="font-medium">{{ $order->ordered_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Lần khám</div>
                <a href="{{ route('encounters.show', $order->encounter_id) }}" class="text-blue-600 hover:underline text-sm">
                    Xem lần khám →
                </a>
            </div>
        </div>

        @if($order->clinical_indication)
            <div class="mt-5 pt-5 border-t border-slate-100">
                <div class="text-slate-500 text-sm mb-1">Chỉ định lâm sàng</div>
                <div class="text-sm">{{ $order->clinical_indication }}</div>
            </div>
        @endif

        @if($order->status === 'cancelled' && $order->cancel_reason)
            <div class="mt-5 pt-5 border-t border-slate-100">
                <div class="text-red-500 text-sm mb-1">Lý do huỷ</div>
                <div class="text-sm">{{ $order->cancel_reason }}</div>
            </div>
        @endif
    </div>

    @if(!in_array($order->status, ['cancelled','completed']))
        <form method="POST" action="{{ route('orders.destroy', $order->id) }}"
              onsubmit="return confirm('Xác nhận huỷ lệnh này?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600 border border-red-300 px-4 py-2 rounded-lg text-sm hover:bg-red-50">
                Huỷ lệnh
            </button>
        </form>
    @endif
</div>
@endsection
