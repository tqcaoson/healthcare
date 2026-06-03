@extends('layouts.app')
@section('page-title', 'Sửa kết quả xét nghiệm')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('results.show', $result->id) }}" class="text-slate-500 hover:text-slate-800">← Chi tiết</a>
        <h1 class="text-2xl font-bold text-slate-800">Sửa kết quả xét nghiệm</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $demo = $result->patient?->currentDemographics; @endphp
    <div class="bg-slate-50 rounded-lg px-4 py-2 text-sm text-slate-600">
        Bệnh nhân: <span class="font-bold">{{ $demo?->family_name }} {{ $demo?->given_name }}</span> —
        XN: <span class="font-bold">{{ $result->loinc_display }}</span>
    </div>

    <form method="POST" action="{{ route('results.update', $result->id) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Giá trị số</label>
                <input type="number" step="0.001" name="value_quantity"
                       value="{{ old('value_quantity', $result->value_quantity) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Đơn vị</label>
                <input type="text" name="value_unit" value="{{ old('value_unit', $result->value_unit) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cờ bất thường *</label>
                <select name="abnormal_flag"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    @foreach(['N'=>'N — Bình thường','H'=>'H — Cao','L'=>'L — Thấp','HH'=>'HH — Rất cao','LL'=>'LL — Rất thấp','A'=>'A — Bất thường','null'=>'N/A'] as $val => $label)
                        <option value="{{ $val }}" {{ old('abnormal_flag', $result->abnormal_flag) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái *</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    @foreach(['registered','preliminary','final','corrected','amended','cancelled'] as $s)
                        <option value="{{ $s }}" {{ old('status', $result->status) === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('results.show', $result->id) }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">Huỷ</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Cập nhật
            </button>
        </div>
    </form>
</div>
@endsection
