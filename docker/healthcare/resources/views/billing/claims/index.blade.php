@extends('layouts.app')

@section('page-title', 'Claims')

@section('content')

<div
    x-data="claimsManager"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center">

            <h2 class="text-xl font-semibold">
                Insurance Claims
            </h2>

            <button class="btn-primary">
                Generate Claim
            </button>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-5">

        <div class="metric-card">
            <div class="metric-title">
                Submitted
            </div>

            <div class="metric-value">
                143
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Pending
            </div>

            <div class="metric-value">
                22
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Rejected
            </div>

            <div class="metric-value">
                8
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Paid
            </div>

            <div class="metric-value">
                $120K
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-slate-50">

                <tr>

                    <th class="table-head">
                        Claim #
                    </th>

                    <th class="table-head">
                        Patient
                    </th>

                    <th class="table-head">
                        Amount
                    </th>

                    <th class="table-head">
                        Status
                    </th>

                </tr>

            </thead>

            <tbody>

                <template x-for="claim in claims">

                    <tr class="border-t">

                        <td class="table-cell">
                            <span x-text="claim.number"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="claim.patient"></span>
                        </td>

                        <td class="table-cell">
                            $
                            <span x-text="claim.amount"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="claim.status"></span>
                        </td>

                    </tr>

                </template>

            </tbody>
        </table>
    </div>
</div>

@endsection