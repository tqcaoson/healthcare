@extends('layouts.app')

@section('page-title', 'MAR Scan — Phát thuốc tại giường')

@section('content')

<div
    x-data="medicationScan"
    data-encounter-id="{{ $encounterId ?? '' }}"
    data-patient-id="{{ $encounter?->patient_id ?? '' }}"
    class="space-y-6"
>

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">MAR Scan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Phát thuốc tại giường — xác minh 5 Rights bằng barcode</p>
        </div>

        @if($encounter)
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-2 text-sm text-blue-800 font-medium">
                {{ $encounter->patient->currentDemographics?->family_name }}
                {{ $encounter->patient->currentDemographics?->given_name }}
                &mdash; MRN: {{ $encounter->patient->mrn }}
            </div>
        @endif
    </div>

    {{-- ── Error (encounter không hợp lệ) ── --}}
    @if(!empty($encounterError))
    <div class="bg-red-50 border border-red-200 rounded-2xl px-6 py-4 text-sm text-red-700 font-medium">
        ⚠ {{ $encounterError }}
    </div>
    @endif

    {{-- ── Encounter selector (khi chưa chọn encounter) ── --}}
    @unless($encounter)
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-700 mb-3">Chọn lần khám cần phát thuốc</h2>
        <div class="flex gap-3">
            <select
                id="encounter-select"
                class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                onchange="if(this.value) window.location.href = '{{ route('pharmacy.medication-scan') }}?encounter_id=' + this.value"
            >
                <option value="">-- Chọn bệnh nhân / lần khám --</option>
                @foreach($activeEncounters as $enc)
                    <option value="{{ $enc['id'] }}">{{ $enc['label'] }}</option>
                @endforeach
            </select>
        </div>
        @if($activeEncounters->isEmpty())
            <p class="text-sm text-gray-400 mt-3">Không có lần khám nội trú nào đang hoạt động.</p>
        @endif
    </div>
    @endunless

    {{-- ── MAR Schedule ── --}}
    <div x-show="schedule.length > 0 && step === 'idle'" class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-700">Lịch phát thuốc</h2>
            <button @click="loadSchedule" class="text-xs text-blue-600 hover:underline">↻ Làm mới</button>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Thuốc</th>
                    <th class="px-5 py-3 text-left">Liều</th>
                    <th class="px-5 py-3 text-left">Đường / Tần suất</th>
                    <th class="px-5 py-3 text-center">Dược sĩ</th>
                    <th class="px-5 py-3 text-center">Trạng thái</th>
                    <th class="px-5 py-3 text-left">Phát gần nhất</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <template x-for="item in schedule" :key="item.order_id">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900" x-text="item.drug_name"></td>
                        <td class="px-5 py-3 text-gray-600">
                            <span x-text="item.dose_value"></span>
                            <span x-text="item.dose_unit"></span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            <span x-text="item.route"></span> /
                            <span x-text="item.frequency"></span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span
                                :class="item.is_verified_by_pharmacist ? 'text-green-600' : 'text-amber-500'"
                                x-text="item.is_verified_by_pharmacist ? '✓' : '⏳'"
                            ></span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span
                                x-show="item.is_overdue"
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                            >OVERDUE</span>
                            <span
                                x-show="item.is_due && !item.is_overdue"
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700"
                            >DUE</span>
                            <span
                                x-show="!item.is_due && !item.is_overdue"
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500"
                            >Scheduled</span>
                        </td>
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            <span x-text="item.last_administered_at
                                ? new Date(item.last_administered_at).toLocaleString('vi-VN')
                                : '—'"></span>
                        </td>
                        <td class="px-5 py-3">
                            <button
                                @click="selectOrder(item)"
                                :disabled="!item.is_verified_by_pharmacist"
                                :class="!item.is_verified_by_pharmacist
                                    ? 'opacity-40 cursor-not-allowed text-gray-400'
                                    : 'text-blue-600 hover:text-blue-800 font-medium'"
                                class="text-sm"
                            >
                                Phát thuốc →
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- ── Scan flow (hiện sau khi chọn order) ── --}}
    <div
        x-show="step !== 'idle' && step !== 'done'"
        class="grid grid-cols-2 gap-6 md:grid-cols-1 lg:grid-cols-2"
    >

        {{-- Camera / Scanner --}}
        <div class="space-y-4">

            {{-- Step indicator --}}
            <div class="flex gap-2 text-sm font-medium">
                <span
                    :class="step === 'scanning-patient' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400'"
                    class="px-3 py-1 rounded-full transition-colors"
                >1. Quét bệnh nhân</span>
                <span
                    :class="step === 'scanning-medication' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400'"
                    class="px-3 py-1 rounded-full transition-colors"
                >2. Quét thuốc</span>
                <span
                    :class="step === 'ready' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-400'"
                    class="px-3 py-1 rounded-full transition-colors"
                >3. Xác nhận</span>
            </div>

            {{-- Selected drug info --}}
            <div x-show="selectedOrder" class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm">
                <p class="font-semibold text-blue-900" x-text="selectedOrder?.drug_name"></p>
                <p class="text-blue-700 mt-0.5">
                    <span x-text="selectedOrder?.dose_value"></span>
                    <span x-text="selectedOrder?.dose_unit"></span>
                    — <span x-text="selectedOrder?.route"></span>
                </p>
            </div>

            {{-- Barcode input (manual fallback + camera) --}}
            <div
                x-data="barcodeScanner"
                class="bg-black rounded-2xl overflow-hidden relative"
            >
                <video
                    x-ref="video"
                    autoplay
                    playsinline
                    class="w-full h-[320px] object-cover"
                ></video>

                {{-- Scan frame --}}
                <div class="absolute inset-0 border-[3px] border-red-500 m-16 rounded-2xl pointer-events-none"></div>

                <div class="absolute bottom-4 left-4 right-4 space-y-2">
                    <div class="bg-white/90 backdrop-blur rounded-xl px-4 py-2.5 text-center text-sm font-semibold text-gray-800">
                        <span x-text="result || ($parent.step === 'scanning-patient' ? 'Quét vòng tay bệnh nhân...' : 'Quét hộp/lọ thuốc...')"></span>
                    </div>

                    {{-- Manual input --}}
                    <input
                        type="text"
                        placeholder="Hoặc nhập barcode thủ công, nhấn Enter"
                        class="w-full bg-white/90 backdrop-blur rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @keydown.enter="
                            if ($parent.step === 'scanning-patient') {
                                $dispatch('patient-scan', { barcode: $el.value });
                            } else if ($parent.step === 'scanning-medication') {
                                $dispatch('medication-scan', { barcode: $el.value });
                            }
                            $el.value = '';
                        "
                    />
                </div>
            </div>

            <button
                @click="resetChecks(); step = 'idle'"
                class="text-sm text-gray-400 hover:text-gray-600"
            >← Quay lại danh sách</button>
        </div>

        {{-- Verification Panel --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col">

            <h2 class="text-xl font-semibold mb-5 text-gray-800">Xác minh 5 Rights</h2>

            <div class="space-y-3 flex-1">

                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-700">Right Patient</span>
                    <span
                        :class="checks.patient ? 'text-green-600' : 'text-gray-300'"
                        class="text-xl font-bold"
                        x-text="checks.patient ? '✓' : '○'"
                    ></span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-700">Right Medication</span>
                    <span
                        :class="checks.medication ? 'text-green-600' : 'text-gray-300'"
                        class="text-xl font-bold"
                        x-text="checks.medication ? '✓' : '○'"
                    ></span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-700">Right Dose</span>
                    <span
                        :class="checks.dose ? 'text-green-600' : 'text-gray-300'"
                        class="text-xl font-bold"
                        x-text="checks.dose ? '✓' : '○'"
                    ></span>
                </div>

                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700">Right Time</span>
                    <span
                        :class="checks.schedule ? 'text-green-600' : (checks.medication ? 'text-amber-500' : 'text-gray-300')"
                        class="text-xl font-bold"
                        x-text="checks.schedule ? '✓' : (checks.medication ? '⚠' : '○')"
                    ></span>
                </div>

                {{-- Patient info snippet --}}
                <div x-show="patientInfo" class="mt-2 bg-green-50 rounded-xl px-3 py-2 text-xs text-green-700">
                    Bệnh nhân xác nhận: MRN <strong x-text="patientInfo?.mrn"></strong>
                </div>

                {{-- Medication info snippet --}}
                <div x-show="medicationInfo" class="bg-green-50 rounded-xl px-3 py-2 text-xs text-green-700">
                    Thuốc: <strong x-text="medicationInfo?.drug_name"></strong>
                    — RxNorm: <span x-text="medicationInfo?.rxnorm_code"></span>
                </div>

                {{-- Error --}}
                <div x-show="errorMessage" class="bg-red-50 text-red-700 rounded-xl px-4 py-3 text-sm font-medium">
                    ⚠ <span x-text="errorMessage"></span>
                </div>

                {{-- Late warning --}}
                <div x-show="checks.medication && !checks.schedule" class="bg-amber-50 text-amber-700 rounded-xl px-3 py-2 text-xs">
                    ⏰ Ngoài cửa sổ giờ phát (±60 phút). Vẫn có thể phát nhưng sẽ được ghi là <em>Late</em>.
                </div>
            </div>

            <button
                type="button"
                class="mt-8 w-full py-3 rounded-xl text-base font-semibold transition-colors"
                :class="allChecksPass || (checks.patient && checks.medication && checks.dose)
                    ? 'bg-blue-600 hover:bg-blue-700 text-white'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                :disabled="!(checks.patient && checks.medication && checks.dose) || administering"
                @click="administer"
            >
                <span x-show="!administering">✓ Administer Medication</span>
                <span x-show="administering">Đang ghi nhận...</span>
            </button>
        </div>
    </div>

    {{-- ── Success screen ── --}}
    <div x-show="step === 'done'" class="bg-white rounded-2xl shadow-sm p-10 text-center">
        <div class="text-5xl mb-4">✅</div>
        <h2 class="text-xl font-bold text-green-700 mb-2">Phát thuốc thành công</h2>
        <p class="text-gray-600 mb-8" x-text="successMessage"></p>
        <div class="flex gap-3 justify-center">
            <button
                @click="resetChecks(); step = 'idle'; loadSchedule()"
                class="px-6 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700"
            >
                Phát thuốc tiếp theo
            </button>
            <a
                href="{{ route('medications.index') }}"
                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50"
            >
                Về danh sách MAR
            </a>
        </div>
    </div>

</div>

{{-- Wire Alpine.js event listeners to medicationScan store --}}
<script>
    document.addEventListener('alpine:init', () => {
        document.addEventListener('patient-scan',    e => Alpine.store && window.dispatchEvent(new CustomEvent('patient-scan-internal',    { detail: e.detail })));
        document.addEventListener('medication-scan', e => Alpine.store && window.dispatchEvent(new CustomEvent('medication-scan-internal', { detail: e.detail })));
    });
</script>

@endsection
