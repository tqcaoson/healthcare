@extends('layouts.app')

@section('page-title', 'Access Denied')

@section('content')

<div class="flex items-center justify-center h-[70vh]">

    <div class="text-center">

        <div class="text-7xl mb-6">
            🔒
        </div>

        <h1 class="text-4xl font-bold">
            Access Denied
        </h1>

        <p class="text-slate-500 mt-4">
            You do not have permission
        </p>

    </div>
</div>

@endsection