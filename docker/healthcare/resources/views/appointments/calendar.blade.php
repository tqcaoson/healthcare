@extends('layouts.app')

@section('page-title', 'Appointment Calendar')

@section('content')

<div
    x-data="appointmentCalendar"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center">

            <h2 class="text-xl font-semibold">
                Weekly Schedule
            </h2>

            <button class="btn-primary">
                New Appointment
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

        <div class="grid grid-cols-7 border-b">

            <template x-for="day in days">

                <div class="p-4 border-r font-semibold text-center">
                    <span x-text="day"></span>
                </div>

            </template>

        </div>

        <div class="grid grid-cols-7 min-h-[700px]">

            <template x-for="column in columns">

                <div class="border-r p-3 space-y-3">

                    <template x-for="appointment in column">

                        <div class="bg-blue-100 rounded-xl p-3">

                            <div class="font-semibold">
                                <span x-text="appointment.patient"></span>
                            </div>

                            <div class="text-sm">
                                <span x-text="appointment.time"></span>
                            </div>
                        </div>

                    </template>

                </div>

            </template>

        </div>
    </div>
</div>

@endsection