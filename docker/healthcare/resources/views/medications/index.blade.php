@extends('layouts.app')
@section('page-title', 'Thuốc đã dùng')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Thuốc đã dùng (MAR)</h1>
        <a href="{{ route('medications.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Ghi nhận thuốc
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('medications.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <select name="status"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                @foreach(['completed','not-done','on-hold','stopped'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="days"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="1"  {{ request('days') == 1  ? 'selected' : '' }}>Hôm nay</option>
                <option value="7"  {{ request('days', 7) == 7 ? 'selected' : '' }}>7 ngày qua</option>
                <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>30 ngày qua</option>
            </select>
            <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800">Lọc</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Bệnh nhân</th>
                    <th class="px-5 py-3 text-left">Thuốc</th>
                    <th class="px-5 py-3 text-left">Liều</th>
                    <th class="px-5 py-3 text-left">Đường dùng</th>
                    <th class="px-5 py-3 text-left">Thời gian</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($administrations as $admin)
                    @php $demo = $admin->patient?->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $demo?->family_name }} {{ $demo?->given_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $admin->patient?->mrn }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ $admin->drug_name }}</div>
                            @if($admin->rxnorm_code)
                                <div class="text-xs text-slate-500 font-mono">RxNorm: {{ $admin->rxnorm_code }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-medium">
                            {{ $admin->dose_value }} {{ $admin->dose_unit }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ ucfirst($admin->route) }}</td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            {{ $admin->administered_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $admin->status === 'completed' ? 'bg-green-100 text-green-700' :
                                   ($admin->status === 'not-done' ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($admin->status) }}
                            </span>
                            @if($admin->is_prn)
                                <span class="ml-1 px-2 py-1 rounded text-xs bg-purple-100 text-purple-700">PRN</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('medications.show', $admin->id) }}" class="text-blue-600 hover:underline text-xs">Xem</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            Chưa có dữ liệu thuốc. <a href="{{ route('medications.create') }}" class="text-blue-600 hover:underline">Ghi nhận ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($administrations->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $administrations->links() }}</div>
        @endif
    </div>
</div>
@endsection
