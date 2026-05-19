@extends('layouts.app')

@section('page-title', 'FHIR Resource Viewer')

@section('content')

<div
    x-data="fhirViewer"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="grid grid-cols-3 gap-4">

            <select
                x-model="resourceType"
                class="form-input"
            >
                <option>Patient</option>
                <option>Observation</option>
                <option>Encounter</option>
                <option>Condition</option>
                <option>MedicationRequest</option>
            </select>

            <input
                x-model="resourceId"
                class="form-input"
                placeholder="FHIR ID"
            >

            <button
                class="btn-primary"
                @click="load"
            >
                Load
            </button>
        </div>
    </div>

    <div class="bg-slate-900 rounded-2xl overflow-hidden">

        <pre
            class="p-6 text-green-400 overflow-x-auto text-sm"
            x-text="json"
        ></pre>

    </div>
</div>

@endsection