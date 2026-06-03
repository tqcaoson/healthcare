@php $demo = $patient->currentDemographics; @endphp

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">

    <div class="flex items-start justify-between">

        <div class="flex gap-5">

            <div
                class="w-20 h-20 rounded-2xl bg-slate-200 flex items-center justify-center text-2xl font-bold"
            >
                {{ $demo ? substr($demo->family_name ?? '?', 0, 1) : '?' }}
            </div>

            <div>

                <h1 class="text-2xl font-bold">
                    @if($demo)
                        {{ $demo->family_name }} {{ $demo->given_name }}
                    @else
                        <span class="text-slate-400 italic">Chưa có thông tin</span>
                    @endif
                </h1>

                <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-600">

                    <div>
                        MRN:
                        <span class="font-semibold">{{ $patient->mrn }}</span>
                    </div>

                    @if($demo)
                        <div>DOB: {{ $demo->birth_date }}</div>
                        <div>Gender: {{ ucfirst($demo->gender ?? '—') }}</div>
                    @endif

                    @if($patient->blood_type)
                        <div>Blood: <span class="font-semibold">{{ $patient->blood_type }}</span></div>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap gap-2">

                    @forelse($patient->conditions as $condition)
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs">
                            {{ $condition->free_text }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 italic">Không có bệnh đang điều trị</span>
                    @endforelse

                </div>
            </div>
        </div>

        <div class="space-y-2 text-right">

            @forelse($patient->allergies as $allergy)
                <div class="bg-red-100 text-red-700 px-3 py-2 rounded-xl text-sm">
                    ⚠ {{ $allergy->substance_display ?? $allergy->substance_name }}
                </div>
            @empty
                <div class="text-xs text-slate-400 italic px-3 py-2 border border-dashed border-slate-300 rounded-xl">
                    NKDA — Không ghi nhận dị ứng
                </div>
            @endforelse

        </div>
    </div>
</div>