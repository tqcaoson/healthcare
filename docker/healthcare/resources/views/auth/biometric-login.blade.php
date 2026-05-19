@extends('layouts.guest')

@section('content')

<div
    x-data="biometricLogin"
    class="min-h-screen flex items-center justify-center"
>

    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-md">

        <div class="text-center">

            <div class="text-6xl mb-5">
                👆
            </div>

            <h1 class="text-2xl font-bold">
                Biometric Login
            </h1>

            <p class="text-slate-500 mt-3">
                Authenticate using Face ID or fingerprint
            </p>

        </div>

        <button
            class="btn-primary w-full mt-8"
            @click="login"
        >
            Authenticate
        </button>
    </div>
</div>

@endsection