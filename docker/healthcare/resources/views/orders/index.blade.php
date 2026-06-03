@extends('layouts.app')
@section('page-title', 'Danh sách lệnh')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Lệnh (Orders)</h1>
        <a href="{{ route('orders.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Đặt lệnh mới
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('orders.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <select name="order_type"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả loại</option>
                @foreach(['lab','radiology','medication','nursing','diet','referral','procedure'] as $t)
                    <option value="{{ $t }}" {{ request('order_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <select name="status"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                @foreach(['pending','active','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800">Lọc</button>
            @if(request()->hasAny(['order_type','status']))
                <a href="{{ route('orders.index') }}" class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">Xoá lọc</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Mã lệnh</th>
                    <th class="px-5 py-3 text-left">Bệnh nhân</th>
                    <th class="px-5 py-3 text-left">Loại</th>
                    <th class="px-5 py-3 text-left">Ưu tiên</th>
                    <th class="px-5 py-3 text-left">Chỉ định</th>
                    <th class="px-5 py-3 text-left">Đặt lúc</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    @php $demo = $order->patient?->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-mono text-xs text-blue-700">{{ $order->order_number }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
                            <div class="text-xs text-slate-500">{{ $order->patient?->mrn }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                {{ ucfirst($order->order_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $order->priority === 'stat' ? 'bg-red-100 text-red-700' :
                                   ($order->priority === 'urgent' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ strtoupper($order->priority) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 max-w-xs truncate">
                            {{ $order->clinical_indication ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            {{ $order->ordered_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $order->status === 'active' ? 'bg-green-100 text-green-700' :
                                   ($order->status === 'completed' ? 'bg-blue-100 text-blue-700' :
                                   ($order->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-blue-600 hover:underline text-xs">Xem</a>
                                @if(!in_array($order->status, ['cancelled','completed']))
                                    <a href="{{ route('orders.edit', $order->id) }}" class="text-amber-600 hover:underline text-xs">Sửa</a>
                                    <form method="POST" action="{{ route('orders.destroy', $order->id) }}"
                                          onsubmit="return confirm('Huỷ lệnh này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline text-xs">Huỷ</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                            Chưa có lệnh nào. <a href="{{ route('orders.create') }}" class="text-blue-600 hover:underline">Đặt lệnh ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
