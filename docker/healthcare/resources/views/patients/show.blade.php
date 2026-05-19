@extends('layouts.app')

@section('page-title', 'Patient Chart')

@section('content')

<div class="space-y-6">

    {{-- Patient Banner --}}
    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between">

            <div>
                <h1 class="text-2xl font-bold">
                    {{ $patient->currentDemographics->family_name }}
                    {{ $patient->currentDemographics->given_name }}
                </h1>

                <div class="mt-2 text-slate-600">
                    MRN: {{ $patient->mrn }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-red-600 font-semibold">
                    Allergies
                </div>

                @foreach($patient->allergies as $allergy)
                    <div>
                        {{ $allergy->substance_name }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="bg-white rounded-2xl shadow-sm p-6">

        <h2 class="text-lg font-semibold mb-5">
            Clinical Timeline
        </h2>

        <div class="space-y-4">

            @foreach($encounters as $encounter)

                <div class="border rounded-xl p-4">

                    <div class="font-semibold">
                        {{ $encounter->reason_text }}
                    </div>

                    <div class="text-sm text-slate-500">
                        {{ $encounter->period_start }}
                    </div>
                </div>

            @endforeach

        </div>
    </div>
</div>

@endsection