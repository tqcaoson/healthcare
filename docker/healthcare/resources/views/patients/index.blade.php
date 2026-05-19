@extends('layouts.app')

@section('page-title', 'Patient Search')

@section('content')

<div
    x-data="patientSearch"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="grid grid-cols-3 gap-4">

            <input
                x-model="query"
                type="text"
                placeholder="Search patient..."
                class="form-input"
            >

            <input
                x-model="mrn"
                type="text"
                placeholder="MRN"
                class="form-input"
            >

            <button
                @click="search"
                class="btn-primary"
            >
                Search
            </button>
        </div>
    </div>

    <div class="space-y-4">

        <template x-for="patient in patients">

            <div class="bg-white rounded-xl p-5 shadow-sm">

                <div class="flex justify-between">

                    <div>
                        <h3
                            class="font-semibold text-lg"
                            x-text="patient.full_name"
                        ></h3>

                        <p class="text-slate-500">
                            MRN:
                            <span x-text="patient.mrn"></span>
                        </p>
                    </div>

                    <div>
                        <a
                            :href="`/patients/${patient.id}`"
                            class="btn-primary"
                        >
                            Open Chart
                        </a>
                    </div>
                </div>
            </div>

        </template>

    </div>
</div>

@endsection