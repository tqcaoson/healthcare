@extends('layouts.auth')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-slate-100">

    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold">
                Healthcare HIS
            </h1>

            <p class="text-slate-500 mt-2">
                Secure clinical access
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('login') }}"
            class="space-y-5"
        >
            @csrf

            <div>
                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-input"
                    required
                >
            </div>

            <div>
                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-input"
                    required
                >
            </div>

            <button
                type="submit"
                class="btn-primary w-full"
            >
                Sign In
            </button>
        </form>
    </div>
</div>

@endsection