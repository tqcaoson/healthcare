@extends('layouts.app')

@section('page-title', 'Document Viewer')

@section('content')

<div
    x-data="documentViewer"
    class="space-y-6"
>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex justify-between items-center">

        <div class="font-semibold">
            <span x-text="document.name"></span>
        </div>

        <div class="flex gap-3">

            <button
                class="btn-secondary"
                @click="download"
            >
                Download
            </button>

            <button
                class="btn-primary"
                @click="print"
            >
                Print
            </button>

        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden h-[80vh]">

        <iframe
            :src="document.url"
            class="w-full h-full"
        ></iframe>

    </div>
</div>

@endsection