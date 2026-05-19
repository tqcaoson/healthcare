@extends('layouts.app')

@section('page-title', 'Reception Queue')

@section('content')

<div
    x-data="queueBoard"
    class="space-y-6"
>

    <div class="grid grid-cols-4 gap-5">

        <div class="metric-card">
            <div class="metric-title">
                Waiting
            </div>

            <div class="metric-value">
                <span x-text="stats.waiting"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                Checked In
            </div>

            <div class="metric-value">
                <span x-text="stats.checkedIn"></span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">
                In Progress
            </div>

            <div class="metric-value">
                <span x-text="stats.inProgress"></span>
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

    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-slate-50">

                <tr>

                    <th class="table-head">
                        Queue #
                    </th>

                    <th class="table-head">
                        Patient
                    </th>

                    <th class="table-head">
                        Department
                    </th>

                    <th class="table-head">
                        Status
                    </th>

                    <th class="table-head"></th>

                </tr>

            </thead>

            <tbody>

                <template x-for="item in queue">

                    <tr class="border-t">

                        <td class="table-cell">
                            <span x-text="item.number"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="item.patient"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="item.department"></span>
                        </td>

                        <td class="table-cell">

                            <span
                                class="px-3 py-1 rounded-full text-xs"
                                :class="{
                                    'bg-amber-100 text-amber-700': item.status === 'WAITING',
                                    'bg-blue-100 text-blue-700': item.status === 'IN_PROGRESS',
                                    'bg-green-100 text-green-700': item.status === 'DONE',
                                }"
                            >
                                <span x-text="item.status"></span>
                            </span>

                        </td>

                        <td class="table-cell text-right">

                            <button
                                class="btn-primary"
                                @click="call(item)"
                            >
                                Call
                            </button>

                        </td>

                    </tr>

                </template>

            </tbody>
        </table>
    </div>
</div>

@endsection