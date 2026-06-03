@extends('layouts.app')
@section('page-title', 'Chi tiết lần khám')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('encounters.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
            <h1 class="text-2xl font-bold text-slate-800">Chi tiết lần khám</h1>
        </div>
        <a href="{{ route('encounters.edit', $encounter->id) }}"
           class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Chỉnh sửa
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $encounter->patient?->currentDemographics; @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
        <div class="flex justify-between items-center">
            <div>
                <div class="font-bold text-lg text-blue-900">
                    {{ $demo?->family_name }} {{ $demo?->given_name }}
                </div>
                <div class="text-sm text-blue-700 mt-1">
                    MRN: {{ $encounter->patient?->mrn }}
                    @if($demo?->birth_date)
                        · {{ \Carbon\Carbon::parse($demo->birth_date)->format('d/m/Y') }}
                    @endif
                </div>
            </div>
            <a href="{{ route('patients.show', $encounter->patient_id) }}"
               class="text-sm text-blue-600 hover:underline">Xem hồ sơ →</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-slate-700 mb-4">Thông tin lần khám</h3>
        <div class="grid grid-cols-3 gap-6 text-sm">
            <div>
                <div class="text-slate-500 mb-1">Loại</div>
                <div class="font-medium">{{ ucfirst($encounter->encounter_type) }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Trạng thái</div>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    {{ $encounter->status === 'in-progress' ? 'bg-green-100 text-green-700' :
                       ($encounter->status === 'finished' ? 'bg-slate-100 text-slate-600' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($encounter->status) }}
                </span>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Class</div>
                <div class="font-medium">{{ $encounter->class }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Bắt đầu</div>
                <div class="font-medium">{{ $encounter->period_start?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Kết thúc</div>
                <div class="font-medium">{{ $encounter->period_end?->format('d/m/Y H:i') ?? 'Đang điều trị' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Lý do</div>
                <div class="font-medium">{{ $encounter->reason_text ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-slate-700 mb-4">Chẩn đoán</h3>
        @forelse($encounter->diagnoses as $diag)
            <div class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                <span class="font-mono text-blue-700">{{ $diag->icd10_code }}</span>
                <span>{{ $diag->icd10_display }}</span>
                <span class="ml-auto text-xs text-slate-500">{{ ucfirst($diag->diagnosis_type) }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Chưa có chẩn đoán.</p>
        @endforelse
    </div>

    <div class="flex gap-3">
        <a href="{{ route('orders.create') }}?encounter_id={{ $encounter->id }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Đặt lệnh (Orders)
        </a>
        <a href="{{ route('results.index') }}"
           class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
            Xem kết quả xét nghiệm
        </a>
    </div>
</div>
@endsection
