@extends('layouts.app')
@section('page-title', 'Danh sách bệnh nhân')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Bệnh nhân</h1>
        <a href="{{ route('patients.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Thêm bệnh nhân
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('patients.index') }}" class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm theo tên hoặc MRN..."
                   class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5">
                🔍 Tìm kiếm
            </button>
            @if(request('search'))
                <a href="{{ route('patients.index') }}"
                   class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
                    Xoá lọc
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">MRN</th>
                    <th class="px-5 py-3 text-left">Họ tên</th>
                    <th class="px-5 py-3 text-left">Ngày sinh</th>
                    <th class="px-5 py-3 text-left">Giới tính</th>
                    <th class="px-5 py-3 text-left">SĐT</th>
                    <th class="px-5 py-3 text-left">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($patients as $patient)
                    @php $demo = $patient->currentDemographics; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-mono text-blue-700">{{ $patient->mrn }}</td>
                        <td class="px-5 py-3 font-medium">
                            {{ $demo?->family_name }} {{ $demo?->given_name }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ $demo?->birth_date ? \Carbon\Carbon::parse($demo->birth_date)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ match($demo?->gender) {
                                'male'   => 'Nam',
                                'female' => 'Nữ',
                                'other'  => 'Khác',
                                default  => '—',
                            } }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $demo?->phone ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $patient->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($patient->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-3">
                                @can('view', $patient)
                                <a href="{{ route('patients.show', $patient->id) }}"
                                   class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-2.5 py-1 rounded text-xs font-medium">Xem</a>
                                @endcan
                                @can('update', $patient)
                                <a href="{{ route('patients.edit', $patient->id) }}"
                                   class="bg-amber-100 text-amber-700 hover:bg-amber-200 px-2.5 py-1 rounded text-xs font-medium">Sửa</a>
                                @endcan
                                @can('delete', $patient)
                                <form method="POST" action="{{ route('patients.destroy', $patient->id) }}"
                                      onsubmit="return confirm('Vô hiệu hoá bệnh nhân này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-2.5 py-1 rounded text-xs font-medium">Xoá</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            Chưa có bệnh nhân nào. <a href="{{ route('patients.create') }}" class="text-blue-600 hover:underline">Thêm ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($patients->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
