<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css'])

    <title>
        @yield('page-title', config('app.name'))
    </title>
</head>

<body>

    @include('components.session-timeout')

    @include('components.toast-container')

    @include('components.cds-alert-modal')

    @include('components.error-boundary')

    @include('components.pwa-install-banner')

    <div class="flex min-h-screen">

        @include('components.sidebar')

        @include('components.mobile-sidebar')

        <main class="flex-1 p-6">

            @yield('content')

        </main>
    </div>

    @vite(['resources/js/app.js'])

    @stack('scripts')

</body>
</html>
