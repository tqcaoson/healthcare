@extends('layouts.app')

@section('page-title', 'LIS Monitor')

@section('content')

<div
    x-data="lisMonitor"
    class="space-y-6"
>

    <div class="grid grid-cols-4 gap-5">

        <div class="metric-card">
            <div class="metric-title">
                Pending Orders
            </div>

            <div class="metric-value">
                <span x-text="stats.pending"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Processing
            </div>

            <div class="metric-value">
                <span x-text="stats.processing"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Completed
            </div>

            <div class="metric-value">
                <span x-text="stats.completed"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Critical Results
            </div>

            <div class="metric-value">
                <span x-text="stats.critical"></span>
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-slate-50">

                <tr>

                    <th class="table-head">
                        Order #
                    </th>

                    <th class="table-head">
                        Test
                    </th>

                    <th class="table-head">
                        Status
                    </th>

                    <th class="table-head">
                        Priority
                    </th>

                </tr>

            </thead>

            <tbody>

                <template x-for="order in orders">

                    <tr class="border-t">

                        <td class="table-cell">
                            <span x-text="order.number"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="order.test"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="order.status"></span>
                        </td>

                        <td class="table-cell">

                            <span
                                class="px-3 py-1 rounded-full"
                                :class="{
                                    'bg-red-100 text-red-700': order.priority === 'STAT',
                                    'bg-amber-100 text-amber-700': order.priority === 'URGENT',
                                }"
                            >
                                <span x-text="order.priority"></span>
                            </span>

                        </td>

                    </tr>

                </template>

            </tbody>
        </table>
    </div>
</div>

@endsection