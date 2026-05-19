@extends('layouts.app')

@section('page-title', 'Nurse Station')

@section('content')

<div
    x-data="nurseStation"
    class="space-y-6"
>

    <div class="grid grid-cols-4 gap-5">

        <div class="metric-card">
            <div class="metric-title">
                Active Patients
            </div>

            <div class="metric-value">
                <span x-text="metrics.activePatients"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Pending Medications
            </div>

            <div class="metric-value">
                <span x-text="metrics.pendingMeds"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Critical Alerts
            </div>

            <div class="metric-value">
                <span x-text="metrics.criticalAlerts"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Bed Occupancy
            </div>

            <div class="metric-value">
                <span x-text="metrics.bedOccupancy"></span>%
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <h2 class="text-xl font-semibold mb-5">
            Active Ward Patients
        </h2>

        <div class="space-y-4">

            <template x-for="patient in patients">

                <div class="border rounded-2xl p-4">

                    <div class="flex justify-between">

                        <div>

                            <div class="font-semibold">
                                <span x-text="patient.name"></span>
                            </div>

                            <div class="text-sm text-slate-500">
                                Bed:
                                <span x-text="patient.bed"></span>
                            </div>
                        </div>

                        <div>

                            <template x-if="patient.critical">

                                <div class="bg-red-100 text-red-700 px-3 py-1 rounded-full">
                                    Critical
                                </div>

                            </template>

                        </div>
                    </div>
                </div>

            </template>

        </div>
    </div>
</div>

@endsection