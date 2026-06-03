@extends('layouts.app')
@section('page-title', 'Hồ sơ bệnh nhân')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('patients.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
            <h1 class="text-2xl font-bold text-slate-800">Hồ sơ bệnh nhân</h1>
        </div>
        <a href="{{ route('patients.edit', $patient->id) }}"
           class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Chỉnh sửa
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @php $demo = $patient->currentDemographics; @endphp

    {{-- Patient Banner --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ $demo?->family_name }} {{ $demo?->given_name }}
                </h2>
                <div class="mt-2 flex gap-6 text-sm text-slate-600">
                    <span><span class="font-medium">MRN:</span> {{ $patient->mrn }}</span>
                    <span><span class="font-medium">Ngày sinh:</span>
                        {{ $demo?->birth_date ? \Carbon\Carbon::parse($demo->birth_date)->format('d/m/Y') : '—' }}
                    </span>
                    <span><span class="font-medium">Giới tính:</span>
                        @match($demo?->gender)
                            'male'   => 'Nam',
                            'female' => 'Nữ',
                            'other'  => 'Khác',
                            default  => '—',
                        @endmatch
                    </span>
                    @if($demo?->phone)
                        <span><span class="font-medium">ĐT:</span> {{ $demo->phone }}</span>
                    @endif
                    @if($demo?->email)
                        <span><span class="font-medium">Email:</span> {{ $demo->email }}</span>
                    @endif
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                {{ $patient->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                {{ ucfirst($patient->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- Allergies --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-red-600 mb-3">⚠ Dị ứng</h3>
            @forelse($patient->allergies as $allergy)
                <div class="text-sm py-1 border-b border-slate-100 last:border-0">
                    <div class="font-medium">{{ $allergy->substance_name }}</div>
                    <div class="text-slate-500 text-xs">{{ ucfirst($allergy->reaction_severity ?? 'unknown') }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Không có dị ứng đã ghi nhận.</p>
            @endforelse
        </div>

        {{-- Conditions --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3">Bệnh lý hiện tại</h3>
            @forelse($patient->conditions as $condition)
                <div class="text-sm py-1 border-b border-slate-100 last:border-0">
                    <div class="font-medium">{{ $condition->icd10_display ?? $condition->free_text }}</div>
                    <div class="text-slate-500 text-xs">{{ $condition->icd10_code }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Chưa có bệnh lý được ghi nhận.</p>
            @endforelse
        </div>

        {{-- Quick stats --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3">Tổng quan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Số lần khám</span>
                    <span class="font-semibold">{{ $patient->encounters->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Dị ứng</span>
                    <span class="font-semibold {{ $patient->allergies->count() > 0 ? 'text-red-600' : '' }}">
                        {{ $patient->allergies->count() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Bệnh lý</span>
                    <span class="font-semibold">{{ $patient->conditions->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Encounter Timeline --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-slate-800">Lịch sử khám</h3>
            <a href="{{ route('encounters.create') }}?patient_id={{ $patient->id }}"
               class="text-sm text-blue-600 hover:underline">+ Tạo lần khám mới</a>
        </div>
        @forelse($patient->encounters as $encounter)
            <div class="flex items-start gap-4 py-3 border-b border-slate-100 last:border-0">
                <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0
                    {{ $encounter->status === 'in-progress' ? 'bg-green-500' : 'bg-slate-300' }}">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between">
                        <div>
                            <span class="font-medium text-sm">{{ $encounter->reason_text ?? 'Không rõ lý do' }}</span>
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                                {{ $encounter->encounter_type === 'emergency' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($encounter->encounter_type) }}
                            </span>
                        </div>
                        <a href="{{ route('encounters.show', $encounter->id) }}"
                           class="text-xs text-blue-600 hover:underline">Xem chi tiết →</a>
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        {{ $encounter->period_start?->format('d/m/Y H:i') }}
                        @if($encounter->period_end)
                            → {{ $encounter->period_end->format('d/m/Y H:i') }}
                        @else
                            <span class="text-green-600">(Đang điều trị)</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 py-4">Chưa có lần khám nào.</p>
        @endforelse
    </div>

</div>
@endsection
