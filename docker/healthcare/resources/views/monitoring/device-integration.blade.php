@extends('layouts.app')

@section('page-title', 'Medical Device Integration')

@section('content')

<div
    x-data="deviceIntegration"
    class="space-y-6"
>

    <div class="grid grid-cols-4 gap-5">

        <template x-for="device in devices">

            <div class="bg-white rounded-2xl shadow-sm p-5">

                <div class="flex justify-between items-center">

                    <div>

                        <div class="font-semibold text-lg">
                            <span x-text="device.name"></span>
                        </div>

                        <div class="text-sm text-slate-500">
                            <span x-text="device.type"></span>
                        </div>

                    </div>

                    <div
                        class="w-3 h-3 rounded-full"
                        :class="device.connected
                            ? 'bg-green-500'
                            : 'bg-red-500'"
                    ></div>
                </div>

                <div class="mt-5 text-sm text-slate-600">

                    Last Sync:
                    <span x-text="device.lastSync"></span>

                </div>

            </div>

        </template>

    </div>
</div>

@endsection