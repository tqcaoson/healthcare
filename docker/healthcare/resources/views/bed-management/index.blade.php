@extends('layouts.app')

@section('page-title', 'Bed Management')

@section('content')

<div
    x-data="bedManagement"
    class="space-y-6"
>

    <div class="grid grid-cols-5 gap-5">

        <template x-for="bed in beds">

            <div
                class="rounded-2xl p-5 shadow-sm"
                :class="bed.occupied
                    ? 'bg-red-100'
                    : 'bg-green-100'"
            >

                <div class="font-bold text-lg">

                    Bed
                    <span x-text="bed.number"></span>

                </div>

                <div class="mt-2">

                    <template x-if="bed.occupied">

                        <div>

                            <div class="text-sm">
                                Occupied
                            </div>

                            <div class="font-semibold">
                                <span x-text="bed.patient"></span>
                            </div>
                        </div>

                    </template>

                    <template x-if="!bed.occupied">

                        <div class="text-sm">
                            Available
                        </div>

                    </template>
                </div>
            </div>

        </template>

    </div>
</div>

@endsection