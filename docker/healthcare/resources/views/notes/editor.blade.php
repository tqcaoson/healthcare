@extends('layouts.app')

@section('page-title', 'Clinical Note')

@section('content')

<div
    x-data="clinicalNoteEditor"
    class="space-y-6"
>

    <x-patient-banner :patient="$patient" />

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-xl font-semibold">
                SOAP Note
            </h2>

            <button
                class="btn-primary"
                @click="save"
            >
                Save Note
            </button>
        </div>

        <div class="space-y-6">

            <div>

                <label class="form-label">
                    Subjective
                </label>

                <textarea
                    x-model="note.subjective"
                    class="form-textarea"
                    rows="5"
                ></textarea>

            </div>

            <div>

                <label class="form-label">
                    Objective
                </label>

                <textarea
                    x-model="note.objective"
                    class="form-textarea"
                    rows="5"
                ></textarea>

            </div>

            <div>

                <label class="form-label">
                    Assessment
                </label>

                <textarea
                    x-model="note.assessment"
                    class="form-textarea"
                    rows="5"
                ></textarea>

            </div>

            <div>

                <label class="form-label">
                    Plan
                </label>

                <textarea
                    x-model="note.plan"
                    class="form-textarea"
                    rows="5"
                ></textarea>

            </div>
        </div>
    </div>
</div>

@endsection