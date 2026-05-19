@extends('layouts.app')

@section('page-title', 'User Management')

@section('content')

<div
    x-data="userManagement"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center">

            <div>
                <h2 class="text-xl font-semibold">
                    Users
                </h2>

                <p class="text-slate-500 text-sm">
                    Manage clinicians and staff accounts
                </p>
            </div>

            <button
                class="btn-primary"
                @click="openCreateModal"
            >
                Create User
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-slate-50">

                <tr>

                    <th class="table-head">Name</th>

                    <th class="table-head">Email</th>

                    <th class="table-head">Role</th>

                    <th class="table-head">Status</th>

                    <th class="table-head"></th>

                </tr>

            </thead>

            <tbody>

                <template x-for="user in users">

                    <tr class="border-t">

                        <td class="table-cell">
                            <span x-text="user.name"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="user.email"></span>
                        </td>

                        <td class="table-cell">
                            <span x-text="user.role"></span>
                        </td>

                        <td class="table-cell">

                            <span
                                class="px-3 py-1 rounded-full text-xs"
                                :class="user.active
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                            >
                                <span x-text="user.active ? 'Active' : 'Disabled'"></span>
                            </span>

                        </td>

                        <td class="table-cell text-right">

                            <button
                                class="btn-secondary"
                                @click="edit(user)"
                            >
                                Edit
                            </button>

                        </td>

                    </tr>

                </template>

            </tbody>
        </table>
    </div>
</div>

@endsection