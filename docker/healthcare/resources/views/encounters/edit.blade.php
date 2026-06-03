@extends('layouts.app')
@section('page-title', 'Chỉnh sửa lần khám')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('encounters.show', $encounter->id) }}" class="text-slate-500 hover:text-slate-800">← Chi tiết</a>
        <h1 class="text-2xl font-bold text-slate-800">Chỉnh sửa lần khám</h1>
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

    @php $demo = $encounter->patient?->currentDemographics; @endphp
    <div class="bg-slate-50 rounded-lg px-4 py-2 text-sm text-slate-600">
        Bệnh nhân: <span class="font-bold">{{ $demo?->family_name }} {{ $demo?->given_name }}</span>
        ({{ $encounter->patient?->mrn }})
    </div>

    <form method="POST" action="{{ route('encounters.update', $encounter->id) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái *</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    @foreach(['planned','arrived','triaged','in-progress','onleave','finished','cancelled'] as $s)
                        <option value="{{ $s }}" {{ old('status', $encounter->status) === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Thời gian kết thúc</label>
                <input type="datetime-local" name="period_end"
                       value="{{ old('period_end', $encounter->period_end?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Lý do khám</label>
            <textarea name="reason_text" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason_text', $encounter->reason_text) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('encounters.show', $encounter->id) }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">
                Huỷ
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Cập nhật
            </button>
        </div>
    </form>
</div>
@endsection
