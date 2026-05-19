@extends('layouts.app')

@section('page-title', 'Quality Metrics')

@section('content')

<div
    x-data="qualityDashboard"
    class="space-y-6"
>

    <div class="grid grid-cols-4 gap-5">

        <div class="metric-card">

            <div class="metric-title">
                Readmission Rate
            </div>

            <div class="metric-value">
                4.2%
            </div>

        </div>

        <div class="metric-card">

            <div class="metric-title">
                LOS Average
            </div>

            <div class="metric-value">
                3.8d
            </div>

        </div>

        <div class="metric-card">

            <div class="metric-title">
                Medication Errors
            </div>

            <div class="metric-value">
                2
            </div>

        </div>

        <div class="metric-card">

            <div class="metric-title">
                Patient Satisfaction
            </div>

            <div class="metric-value">
                94%
            </div>

        </div>

    </div>

    <div class="grid grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <h2 class="text-lg font-semibold mb-5">
                Admissions Trend
            </h2>

            <div
                data-react-component="PatientVitalsChart"
                data-props='@json($admissionChart)'
                class="h-[350px]"
            ></div>

        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <h2 class="text-lg font-semibold mb-5">
                Mortality Trend
            </h2>

            <div
                data-react-component="PatientVitalsChart"
                data-props='@json($mortalityChart)'
                class="h-[350px]"
            ></div>

        </div>

    </div>
</div>

@endsection