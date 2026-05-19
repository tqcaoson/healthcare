@extends('layouts.app')

@section('page-title', 'Medication Scan')

@section('content')

<div
    x-data="medicationScan"
    class="space-y-6"
>

    <div class="grid grid-cols-2 gap-6">

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

                    <span class="text-green-600">
                        ✓
                    </span>

                </div>

                <div class="verification-row">

                    <span>Medication Match</span>

                    <span class="text-green-600">
                        ✓
                    </span>

                </div>

                <div class="verification-row">

                    <span>Dose Match</span>

                    <span class="text-green-600">
                        ✓
                    </span>

                </div>

                <div class="verification-row">

                    <span>Schedule Match</span>

                    <span class="text-green-600">
                        ✓
                    </span>

                </div>

            </div>

            <button class="btn-primary w-full mt-8">
                Administer Medication
            </button>

        </div>
    </div>
</div>

@endsection