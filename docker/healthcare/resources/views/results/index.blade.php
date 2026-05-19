@extends('layouts.app')

@section('page-title', 'Results Viewer')

@section('content')

<div
    x-data="resultsViewer"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center">

            <h2 class="text-xl font-semibold">
                Laboratory Results
            </h2>

            <select
                x-model="range"
                class="form-input w-48"
            >
                <option value="1">24 Hours</option>
                <option value="7">7 Days</option>
                <option value="30">30 Days</option>
            </select>
        </div>
    </div>

    <template x-for="result in results">

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between">

                <div>

                    <div class="font-semibold text-lg">
                        <span x-text="result.loinc_display"></span>
                    </div>

                    <div class="text-sm text-slate-500">
                        Resulted at:
                        <span x-text="result.resulted_at"></span>
                    </div>
                </div>

                <div>

                    <template x-if="result.abnormal_flag === 'H'">

                        <div class="bg-red-100 text-red-700 px-3 py-1 rounded-full">
                            HIGH
                        </div>

                    </template>

                </div>
            </div>

            <div class="mt-5">

                <div class="grid grid-cols-4 gap-4">

                    <div>
                        <div class="text-sm text-slate-500">
                            Value
                        </div>

                        <div class="font-semibold text-xl">
                            <span x-text="result.value_quantity"></span>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">
                            Unit
                        </div>

                        <div class="font-semibold">
                            <span x-text="result.value_unit"></span>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">
                            Ref Range
                        </div>

                        <div class="font-semibold">
                            <span x-text="result.reference_low"></span>
                            -
                            <span x-text="result.reference_high"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </template>
</div>

@endsection