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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body">
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="container-fluid px-4">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <!-- Logo/Brand con icono -->
                    <div class="text-center mb-5">
                        <a href="/" class="text-decoration-none">
                            <div class="mb-3">
                                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h1 class="display-5 fw-light text-body mb-0">{{ config('app.name') }}</h1>
                        </a>
                    </div>

                    <!-- Card de autenticación -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-global-modals />
</body>
</html>
