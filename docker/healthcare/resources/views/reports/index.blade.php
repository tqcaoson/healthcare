@extends('layouts.app')
@section('page-title', 'Báo cáo & Thống kê')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="text-2xl font-bold text-slate-800">Báo cáo & Thống kê</h1>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-sm text-slate-500 mb-1">Tổng bệnh nhân</div>
            <div class="text-3xl font-bold text-slate-800">{{ $stats['patients_total'] }}</div>
            <div class="text-xs text-green-600 mt-1">+{{ $stats['patients_new_month'] }} tháng này</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-sm text-slate-500 mb-1">Đang điều trị</div>
            <div class="text-3xl font-bold text-blue-600">{{ $stats['encounters_active'] }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $stats['encounters_today'] }} nhập hôm nay</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-sm text-slate-500 mb-1">Lệnh chờ xử lý</div>
            <div class="text-3xl font-bold text-amber-600">{{ $stats['orders_pending'] }}</div>
            @if($stats['orders_stat'] > 0)
                <div class="text-xs text-red-600 mt-1">{{ $stats['orders_stat'] }} lệnh STAT</div>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 {{ $stats['results_critical'] > 0 ? 'bg-red-50 border border-red-200' : '' }}">
            <div class="text-sm text-slate-500 mb-1">KQ nguy kịch chưa xác nhận</div>
            <div class="text-3xl font-bold {{ $stats['results_critical'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $stats['results_critical'] }}
            </div>
            <div class="text-xs text-slate-500 mt-1">{{ $stats['results_today'] }} KQ hôm nay</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">

        {{-- Encounters Trend --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4">Lượt khám 7 ngày qua</h3>
            <div class="space-y-2">
                @php $maxCount = $encountersTrend->max('count') ?: 1; @endphp
                @foreach($encountersTrend as $day)
                    @php
                        $isToday = \Carbon\Carbon::parse($day['date'])->isToday();
                        $hasData = $day['count'] > 0;
                        $pct     = $hasData ? max(4, round($day['count'] / $maxCount * 100)) : 0;
                    @endphp
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-20 text-xs {{ $isToday ? 'text-blue-600 font-semibold' : 'text-slate-500' }}">
                            {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                            @if($isToday)<span class="text-blue-400"> ●</span>@endif
                        </div>
                        <div class="flex-1 rounded-full h-5 overflow-hidden {{ $hasData ? 'bg-blue-100' : 'bg-slate-100' }}">
                            @if($hasData)
                                <div class="bg-blue-500 h-full rounded-full flex items-center justify-end pr-2 transition-all"
                                     style="width: {{ $pct }}%">
                                </div>
                            @else
                                <div class="h-full flex items-center pl-3">
                                    <span class="text-xs text-slate-400 italic">Không có dữ liệu</span>
                                </div>
                            @endif
                        </div>
                        <div class="w-8 text-right text-xs font-semibold {{ $hasData ? 'text-slate-800' : 'text-slate-300' }}">
                            {{ $hasData ? $day['count'] : '—' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Orders Breakdown --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4">Phân loại lệnh (30 ngày qua)</h3>
            @php
                $typeLabels = ['lab'=>'Xét nghiệm','radiology'=>'Hình ảnh','medication'=>'Thuốc',
                               'nursing'=>'Điều dưỡng','diet'=>'Dinh dưỡng','referral'=>'Chuyển khoa','procedure'=>'Thủ thuật'];
                $totalOrders = $ordersByType->sum() ?: 1;
            @endphp
            <div class="space-y-3">
                @foreach($ordersByType as $type => $count)
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-28 text-slate-600">{{ $typeLabels[$type] ?? ucfirst($type) }}</div>
                        <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                            <div class="bg-purple-400 h-full rounded-full"
                                 style="width: {{ round($count / $totalOrders * 100) }}%"></div>
                        </div>
                        <div class="w-8 text-right font-semibold">{{ $count }}</div>
                    </div>
                @endforeach
                @if($ordersByType->isEmpty())
                    <p class="text-slate-400 text-sm">Chưa có dữ liệu trong 30 ngày qua.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Appointments & Summary --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-sm text-slate-500 mb-1">Lịch hẹn hôm nay</div>
            <div class="text-3xl font-bold text-slate-800">{{ $stats['appointments_today'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-sm text-slate-500 mb-1">Lịch hẹn sắp tới</div>
            <div class="text-3xl font-bold text-slate-800">{{ $stats['appointments_booked'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col justify-between">
            <div class="text-sm text-slate-500">Xuất báo cáo</div>
            <div class="space-y-2 mt-3">
                <a href="{{ route('patients.index') }}"
                   class="block text-center border border-slate-300 text-slate-600 px-3 py-1.5 rounded-lg text-xs hover:bg-slate-50">
                    Danh sách bệnh nhân
                </a>
                <a href="{{ route('encounters.index') }}"
                   class="block text-center border border-slate-300 text-slate-600 px-3 py-1.5 rounded-lg text-xs hover:bg-slate-50">
                    Danh sách lần khám
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
