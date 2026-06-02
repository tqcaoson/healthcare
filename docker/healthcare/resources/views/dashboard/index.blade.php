@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Tổng bệnh nhân</p>
            <p class="text-3xl font-bold mt-1">{{ $metrics['patients_total'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Đang điều trị</p>
            <p class="text-3xl font-bold mt-1">{{ $metrics['active_encounters'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Nhập viện hôm nay</p>
            <p class="text-3xl font-bold mt-1">{{ $metrics['admissions_today'] }}</p>
        </div>
    </div>
</div>
@endsection
