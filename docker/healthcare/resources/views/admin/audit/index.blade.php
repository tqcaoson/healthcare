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
                aria-label="Lọc theo người dùng"
                placeholder="User"
            >

            <input
                x-model="filters.resource"
                class="form-input"
                aria-label="Lọc theo resource"
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

    <div x-show="loading" class="text-center py-10 text-slate-500">Đang tải...</div>

    <div x-show="error" class="bg-red-50 text-red-700 rounded-2xl p-5" x-text="error"></div>

    <div x-show="!loading && !error && logs.length === 0" class="bg-white rounded-2xl shadow-sm p-10 text-center text-slate-400">
        Không có audit log phù hợp.
    </div>

    <div class="space-y-4" x-show="!loading && logs.length > 0">

        <template x-for="log in logs" :key="log.id">

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