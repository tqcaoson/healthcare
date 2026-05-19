@extends('layouts.app')

@section('page-title', 'Medication Administration')

@section('content')

<div class="space-y-6">

    <x-patient-banner :patient="$patient" />

    @foreach($medications as $medication)

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between items-start">

                <div>

                    <h2 class="text-xl font-semibold">
                        {{ $medication->drug_name }}
                    </h2>

                    <div class="mt-2 text-slate-600">
                        {{ $medication->dose_value }}
                        {{ $medication->dose_unit }}
                    </div>

                    <div class="text-slate-600">
                        {{ $medication->frequency }}
                    </div>
                </div>

                <div>

                    @if($medication->is_due)

                        <div class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full">
                            Due
                        </div>

                    @endif

                </div>
            </div>

            <div class="mt-6 flex gap-3">

                <button class="btn-primary">
                    Mark as Given
                </button>

                <button class="btn-secondary">
                    Hold
                </button>

                <button class="btn-danger">
                    Refuse
                </button>
            </div>
        </div>

    @endforeach

</div>

@endsection