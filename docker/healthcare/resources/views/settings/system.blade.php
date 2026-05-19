@extends('layouts.app')

@section('page-title', 'System Settings')

@section('content')

<div
    x-data="systemSettings"
    class="space-y-6 max-w-5xl"
>

    <div class="bg-white rounded-2xl shadow-sm p-6">

        <h2 class="text-xl font-semibold mb-6">
            General Settings
        </h2>

        <div class="space-y-5">

            <div>

                <label class="form-label">
                    Facility Name
                </label>

                <input
                    x-model="settings.facilityName"
                    class="form-input"
                >

            </div>

            <div>

                <label class="form-label">
                    Timezone
                </label>

                <select
                    x-model="settings.timezone"
                    class="form-input"
                >
                    <option>Asia/Tokyo</option>
                    <option>UTC</option>
                </select>

            </div>

            <div>

                <label class="form-label">
                    Maintenance Mode
                </label>

                <div class="flex items-center gap-3">

                    <input
                        type="checkbox"
                        x-model="settings.maintenance"
                    >

                    <span class="text-sm">
                        Enable maintenance mode
                    </span>

                </div>
            </div>

        </div>

        <div class="mt-8">

            <button
                class="btn-primary"
                @click="save"
            >
                Save Settings
            </button>

        </div>
    </div>
</div>

@endsection