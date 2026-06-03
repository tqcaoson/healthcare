@extends('layouts.app')
@section('page-title', 'Thêm bệnh nhân mới')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('patients.index') }}" class="text-slate-500 hover:text-slate-800">← Quay lại</a>
        <h1 class="text-2xl font-bold text-slate-800">Thêm bệnh nhân mới</h1>
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

    <form method="POST" action="{{ route('patients.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Họ (Family name) *</label>
                <input type="text" name="family_name" value="{{ old('family_name') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Nguyễn" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tên (Given name) *</label>
                <input type="text" name="given_name" value="{{ old('given_name') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Văn An" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh *</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Giới tính *</label>
                <select name="gender"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="">-- Chọn --</option>
                    <option value="male"    {{ old('gender') === 'male'    ? 'selected' : '' }}>Nam</option>
                    <option value="female"  {{ old('gender') === 'female'  ? 'selected' : '' }}>Nữ</option>
                    <option value="other"   {{ old('gender') === 'other'   ? 'selected' : '' }}>Khác</option>
                    <option value="unknown" {{ old('gender') === 'unknown' ? 'selected' : '' }}>Không xác định</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="0901234567">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="example@email.com">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('patients.index') }}"
               class="border border-slate-300 text-slate-600 px-5 py-2 rounded-lg text-sm hover:bg-slate-50">
                Huỷ
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                Lưu bệnh nhân
            </button>
        </div>
    </form>
</div>
@endsection
