@extends('layouts.app')
@section('page-title', 'Chỉnh sửa bệnh nhân')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('patients.show', $patient->id) }}" class="text-slate-500 hover:text-slate-800">← Hồ sơ</a>
        <h1 class="text-2xl font-bold text-slate-800">Chỉnh sửa bệnh nhân</h1>
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

    @php $demo = $patient->currentDemographics; @endphp

    <form method="POST" action="{{ route('patients.update', $patient->id) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="bg-slate-50 rounded-lg px-4 py-2 text-sm text-slate-600">
            MRN: <span class="font-mono font-bold">{{ $patient->mrn }}</span>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Họ (Family name) *</label>
                <input type="text" name="family_name" value="{{ old('family_name', $demo?->family_name) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tên (Given name) *</label>
                <input type="text" name="given_name" value="{{ old('given_name', $demo?->given_name) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh *</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', $demo?->birth_date) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Giới tính *</label>
                <select name="gender"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="male"    {{ old('gender', $demo?->gender) === 'male'    ? 'selected' : '' }}>Nam</option>
                    <option value="female"  {{ old('gender', $demo?->gender) === 'female'  ? 'selected' : '' }}>Nữ</option>
                    <option value="other"   {{ old('gender', $demo?->gender) === 'other'   ? 'selected' : '' }}>Khác</option>
                    <option value="unknown" {{ old('gender', $demo?->gender) === 'unknown' ? 'selected' : '' }}>Không xác định</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $demo?->phone) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $demo?->email) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('patients.show', $patient->id) }}"
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
