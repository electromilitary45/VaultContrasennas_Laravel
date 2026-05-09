<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ theme: 'auto' }" x-init="(() => { if (window.themeManager) { theme = window.themeManager.getStoredTheme(); window.themeManager.applyTheme(theme); } })()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Scripts -->
    @auth
    <script>
        window.userId = {{ Auth::id() }};
        window.reverbAppKey = '{{ config('broadcasting.connections.reverb.key') }}';
        window.reverbHost = '{{ config('broadcasting.connections.reverb.options.host') }}';
        window.reverbPort = {{ config('broadcasting.connections.reverb.options.port') }};
        window.reverbScheme = '{{ config('broadcasting.connections.reverb.options.scheme') }}';
    </script>
    @endauth
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body">
    <div class="min-vh-100 d-flex flex-column">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-body-secondary border-bottom py-3">
                <div class="container-fluid px-4">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-grow-1 py-4">
            <div class="container-fluid px-4">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-global-modals />
    @stack('scripts')
</body>
</html>
