@extends('layouts.app')

@section('page-title', 'Encounter')

@section('content')

<div class="space-y-6">

    <x-patient-banner :patient="$patient" />

    <div class="grid grid-cols-3 gap-6">

        <div class="col-span-2 space-y-5">

            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-lg font-semibold mb-5">
                    Encounter Summary
                </h2>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <div class="text-sm text-slate-500">
                            Encounter Type
                        </div>

                        <div class="font-semibold">
                            {{ $encounter->encounter_type }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">
                            Status
                        </div>

                        <div class="font-semibold">
                            {{ $encounter->status }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">
                            Department
                        </div>

                        <div class="font-semibold">
                            {{ $encounter->department->name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-500">
                            Started At
                        </div>

                        <div class="font-semibold">
                            {{ $encounter->period_start }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-lg font-semibold mb-5">
                    Clinical Timeline
                </h2>

                <div class="space-y-4">

                    @foreach($timeline as $item)

                        <div class="border-l-4 border-blue-500 pl-4 py-2">

                            <div class="text-sm text-slate-500">
                                {{ $item['time'] }}
                            </div>

                            <div class="font-semibold">
                                {{ $item['title'] }}
                            </div>

                            <div class="text-slate-600 text-sm">
                                {{ $item['description'] }}
                            </div>
                        </div>

                    @endforeach

                </div>
            </div>
        </div>

        <div class="space-y-5">

            @include('components.vitals-widget')

            @include('components.notification-inbox')

        </div>
    </div>
</div>

@endsection