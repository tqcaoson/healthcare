@extends('layouts.app')

@section('page-title', 'Audit Explorer')

@section('content')

<div
    x-data="auditExplorer"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <div class="grid grid-cols-4 gap-4">

            <input
                x-model="filters.user"
                class="form-input"
                placeholder="User"
            >

            <input
                x-model="filters.resource"
                class="form-input"
                placeholder="Resource"
            >

            <select
                x-model="filters.action"
                class="form-input"
            >
                <option value="">All Actions</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="view">View</option>
            </select>

            <button
                class="btn-primary"
                @click="search"
            >
                Search
            </button>
        </div>
    </div>

    <div class="space-y-4">

        <template x-for="log in logs">

            <div class="bg-white rounded-2xl shadow-sm p-5">

                <div class="flex justify-between">

                    <div>

                        <div class="font-semibold">

                            <span x-text="log.user"></span>

                            performed

                            <span x-text="log.action"></span>

                        </div>

                        <div class="text-sm text-slate-500">

                            Resource:
                            <span x-text="log.resource"></span>

                        </div>
                    </div>

                    <div class="text-sm text-slate-500">

                        <span x-text="log.created_at"></span>

                    </div>
                </div>
            </div>

        </template>

    </div>
</div>

@endsection