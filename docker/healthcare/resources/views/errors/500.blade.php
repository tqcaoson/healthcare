@extends('layouts.app')

@section('page-title', 'System Error')

@section('content')

<div class="flex items-center justify-center h-[70vh]">

    <div class="text-center">

        <div class="text-7xl mb-6">
            ⚠️
        </div>

        <h1 class="text-4xl font-bold">
            Internal Server Error
        </h1>

        <p class="text-slate-500 mt-4">
            Unexpected application failure occurred
        </p>

        <a
            href="/dashboard"
            class="btn-primary inline-block mt-8"
        >
            Return Dashboard
        </a>

    </div>
</div>

@endsection