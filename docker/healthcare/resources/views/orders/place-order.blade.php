@extends('layouts.app')

@section('page-title', 'Place Order')

@section('content')

<div
    x-data="orderEntry"
    class="max-w-5xl mx-auto space-y-6"
>

    <x-patient-banner :patient="$patient" />

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <h2 class="text-xl font-semibold mb-6">
            Computerized Provider Order Entry
        </h2>

        <div class="grid grid-cols-4 gap-3 mb-6">

            <button
                @click="type='lab'"
                class="order-type-btn"
            >
                🧪 Lab
            </button>

            <button
                @click="type='radiology'"
                class="order-type-btn"
            >
                🩻 Radiology
            </button>

            <button
                @click="type='medication'"
                class="order-type-btn"
            >
                💊 Medication
            </button>

            <button
                @click="type='procedure'"
                class="order-type-btn"
            >
                🏥 Procedure
            </button>
        </div>

        <div class="space-y-5">

            <div>
                <label class="form-label">
                    Search Order
                </label>

                <input
                    x-model="query"
                    @input.debounce.500ms="search"
                    type="text"
                    class="form-input"
                    placeholder="CBC, Troponin, CT Chest..."
                >
            </div>

            <template x-for="item in results">

                <div
                    class="border rounded-xl p-4 hover:bg-slate-50 cursor-pointer"
                    @click="select(item)"
                >

                    <div class="font-semibold">
                        <span x-text="item.name"></span>
                    </div>

                    <div class="text-sm text-slate-500">
                        <span x-text="item.code"></span>
                    </div>
                </div>

            </template>

        </div>

        <div class="mt-8">

            <button
                @click="submit"
                class="btn-primary"
            >
                Place Order
            </button>
        </div>
    </div>
</div>

@endsection