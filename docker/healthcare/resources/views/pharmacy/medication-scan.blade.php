@extends('layouts.app')

@section('page-title', 'Medication Scan')

@section('content')

<div
    x-data="medicationScan"
    class="space-y-6"
>

    <div class="grid grid-cols-2 gap-6 md:grid-cols-1 lg:grid-cols-2">

        <div>

            @include('components.barcode-scanner')

        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <h2 class="text-xl font-semibold mb-5">
                Verification
            </h2>

            <div class="space-y-4">

                <div class="verification-row">
                    <span>Patient Match</span>
                    <span :class="checks.patient ? 'text-green-600' : 'text-red-600'" x-text="checks.patient ? '✓' : '✗'"></span>
                </div>

                <div class="verification-row">
                    <span>Medication Match</span>
                    <span :class="checks.medication ? 'text-green-600' : 'text-red-600'" x-text="checks.medication ? '✓' : '✗'"></span>
                </div>

                <div class="verification-row">
                    <span>Dose Match</span>
                    <span :class="checks.dose ? 'text-green-600' : 'text-red-600'" x-text="checks.dose ? '✓' : '✗'"></span>
                </div>

                <div class="verification-row">
                    <span>Schedule Match</span>
                    <span :class="checks.schedule ? 'text-green-600' : 'text-red-600'" x-text="checks.schedule ? '✓' : '✗'"></span>
                </div>

            </div>

            <div x-show="!allChecksPass" class="mt-4 bg-red-50 text-red-700 rounded-xl px-4 py-3 text-sm">
                ⚠ Một hoặc nhiều kiểm tra chưa vượt qua. Không thể cho thuốc.
            </div>

            <button
                type="button"
                class="btn-primary w-full mt-8"
                :disabled="!allChecksPass || administering"
                :class="(!allChecksPass || administering) ? 'opacity-50 cursor-not-allowed' : ''"
                @click="administer"
            >
                <span x-show="!administering">Administer Medication</span>
                <span x-show="administering">Đang xử lý...</span>
            </button>

        </div>
    </div>
</div>

@endsection