<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="/dashboard-extra.css">

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

        <main class="flex-1 @yield('main-class', 'p-6') overflow-auto">

            @yield('content')

        </main>
    </div>

    @vite(['resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>
