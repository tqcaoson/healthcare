@extends('layouts.app')
@section('page-title', 'Đặt lệnh mới')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('orders.index') }}" class="text-slate-500 hover:text-slate-800">← Danh sách</a>
        <h1 class="text-2xl font-bold text-slate-800">Đặt lệnh mới (CPOE)</h1>
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

    <form method="POST" action="{{ route('orders.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Lần khám *</label>
            <select name="encounter_id"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <option value="">-- Chọn lần khám đang điều trị --</option>
                @foreach($encounters as $enc)
                    @php $d = $enc->patient?->currentDemographics; @endphp
                    <option value="{{ $enc->id }}"
                        {{ old('encounter_id', request('encounter_id')) == $enc->id ? 'selected' : '' }}>
                        {{ $d?->family_name }} {{ $d?->given_name }} — {{ $enc->period_start?->format('d/m/Y') }}
                        ({{ ucfirst($enc->encounter_type) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại lệnh *</label>
                <select name="order_type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="">-- Chọn loại --</option>
                    @foreach(['lab' => 'Xét nghiệm (Lab)', 'radiology' => 'Hình ảnh (Radiology)',
                              'medication' => 'Thuốc (Medication)', 'nursing' => 'Điều dưỡng (Nursing)',
                              'diet' => 'Chế độ ăn (Diet)', 'referral' => 'Chuyển khoa (Referral)',
                              'procedure' => 'Thủ thuật (Procedure)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('order_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mức ưu tiên *</label>
                <select name="priority"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="routine" {{ old('priority', 'routine') === 'routine' ? 'selected' : '' }}>Routine</option>
                    <option value="urgent"  {{ old('priority') === 'urgent'  ? 'selected' : '' }}>Urgent</option>
                    <option value="stat"    {{ old('priority') === 'stat'    ? 'selected' : '' }}>STAT</option>
                    <option value="asap"    {{ old('priority') === 'asap'    ? 'selected' : '' }}>ASAP</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Chỉ định lâm sàng</label>
            <textarea name="clinical_indication" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Mô tả lý do lâm sàng...">{{ old('clinical_indication') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('orders.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">
                Huỷ
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Đặt lệnh
            </button>
        </div>
    </form>
</div>
@endsection
