@extends('layouts.app')

@section('page-title', 'Pharmacy Verification')

@section('content')

<div
    x-data="pharmacyVerification"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <h2 class="text-xl font-semibold">
            Pending Medication Orders
        </h2>
    </div>

    <template x-for="order in orders">

        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between items-start">

                <div>

                    <div class="font-semibold text-lg">
                        <span x-text="order.drug_name"></span>
                    </div>

                    <div class="text-slate-600">
                        <span x-text="order.dose"></span>
                    </div>

                    <div class="text-sm text-slate-500">
                        Ordered by:
                        <span x-text="order.provider"></span>
                    </div>
                </div>

                <div class="flex gap-2">

                    <button
                        class="btn-success"
                        @click="approve(order)"
                    >
                        Verify
                    </button>

                    <button
                        class="btn-danger"
                        @click="reject(order)"
                    >
                        Reject
                    </button>

                </div>
            </div>
        </div>

    </template>
</div>

@endsection