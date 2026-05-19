<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <title>
        @yield('page-title')
    </title>
</head>

<body>

    @include('components.mobile-sidebar')

    @include('components.toast-container')

    @include('components.session-timeout')

    @include('components.cds-alert-modal')

    @include('components.error-boundary')

    @include('components.pwa-install-banner')

    @include('components.toast-container')

    @include('components.session-timeout')

    @include('components.mobile-sidebar')

    <div class="flex min-h-screen">

        @include('components.sidebar')

        <main class="flex-1 p-6">

            @yield('content')

        </main>
    </div>

</body>
</html>