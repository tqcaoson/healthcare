@extends('layouts.app')

@section('page-title', 'DICOM Viewer')

@section('content')

<div
    x-data="dicomViewer"
    class="space-y-6"
>

    <div class="bg-black rounded-2xl overflow-hidden h-[80vh] relative">

        <div
            id="dicomViewport"
            class="w-full h-full"
        ></div>

        <div class="absolute bottom-5 left-5 flex gap-3">

            <button
                class="btn-secondary"
                @click="zoomIn"
            >
                Zoom In
            </button>

            <button
                class="btn-secondary"
                @click="zoomOut"
            >
                Zoom Out
            </button>

            <button
                class="btn-secondary"
                @click="invert"
            >
                Invert
            </button>

        </div>
    </div>
</div>

@endsection