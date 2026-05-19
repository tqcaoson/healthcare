@extends('layouts.app')

@section('page-title', 'Telehealth Session')

@section('content')

<div
    x-data="telehealthSession"
    class="h-[80vh] grid grid-cols-2 gap-6"
>

    <div class="bg-black rounded-2xl relative overflow-hidden">

        <video
            id="remoteVideo"
            autoplay
            playsinline
            class="w-full h-full object-cover"
        ></video>

    </div>

    <div class="bg-slate-900 rounded-2xl relative overflow-hidden">

        <video
            id="localVideo"
            autoplay
            muted
            playsinline
            class="w-full h-full object-cover"
        ></video>

    </div>
</div>

@endsection