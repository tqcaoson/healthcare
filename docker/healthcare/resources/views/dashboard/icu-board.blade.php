@extends('layouts.app')

@section('page-title', 'ICU Monitoring')

@section('content')

<div
    x-data="icuBoard"
    class="grid grid-cols-3 gap-5"
>

    <template x-for="patient in patients">

        <div class="bg-slate-900 text-white rounded-2xl p-5">

            <div class="flex justify-between items-center mb-5">

                <div>

                    <div class="font-bold text-lg">
                        <span x-text="patient.name"></span>
                    </div>

                    <div class="text-sm text-slate-400">
                        Bed
                        <span x-text="patient.bed"></span>
                    </div>
                </div>

                <div
                    class="w-3 h-3 rounded-full"
                    :class="patient.online
                        ? 'bg-green-500'
                        : 'bg-red-500'"
                ></div>
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div class="icu-metric">
                    <div class="icu-label">
                        HR
                    </div>

                    <div class="icu-value">
                        <span x-text="patient.hr"></span>
                    </div>
                </div>

                <div class="icu-metric">
                    <div class="icu-label">
                        SpO2
                    </div>

                    <div class="icu-value">
                        <span x-text="patient.spo2"></span>
                    </div>
                </div>

                <div class="icu-metric">
                    <div class="icu-label">
                        BP
                    </div>

                    <div class="icu-value">
                        <span x-text="patient.bp"></span>
                    </div>
                </div>

                <div class="icu-metric">
                    <div class="icu-label">
                        RR
                    </div>

                    <div class="icu-value">
                        <span x-text="patient.rr"></span>
                    </div>
                </div>

            </div>
        </div>

    </template>

</div>

@endsection