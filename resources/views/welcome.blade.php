<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ theme: 'auto' }" x-init="(() => { if (window.themeManager) { theme = window.themeManager.getStoredTheme(); window.themeManager.applyTheme(theme); } })()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body">
    <div class="container-fluid min-vh-100 d-flex flex-column">
        <!-- Header Minimalista -->
        <header class="py-4">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
                    <div class="container-fluid">
                        <a class="navbar-brand fw-light fs-4 d-flex align-items-center gap-2" href="/">
                            <i class="bi bi-shield-lock-fill text-primary"></i>
                            <span>{{ config('app.name') }}</span>
                        </a>
                        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                            <ul class="navbar-nav align-items-center gap-3">
                                @auth
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center gap-2" href="{{ url('/dashboard') }}">
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Dashboard</span>
                                        </a>
                                    </li>
                                @else
                                    @if (Route::has('login'))
                                        <li class="nav-item">
                                            <a class="nav-link d-flex align-items-center gap-2" href="{{ route('login') }}">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Iniciar Sesión</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (Route::has('register'))
                                        <li class="nav-item">
                                            <a class="nav-link d-flex align-items-center gap-2" href="{{ route('register') }}">
                                                <i class="bi bi-person-plus"></i>
                                                <span>Registrarse</span>
                                            </a>
                                        </li>
                                    @endif
                                @endauth
                                @auth
                                    <!-- Toggle de tema solo para usuarios autenticados -->
                                    <li class="nav-item d-flex align-items-center">
                                        <x-theme-toggle />
                                    </li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <div class="text-center mb-5">
                            <div class="mb-4">
                                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 5rem;"></i>
                            </div>
                            <h1 class="display-1 fw-light mb-4">OCIANN Vault</h1>
                            <p class="lead text-body-secondary mb-5">
                                Un gestor de contraseñas seguro y open source.<br>
                                Diseñado con simplicidad y elegancia.
                            </p>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-5">
                                <h2 class="h3 fw-light mb-4 d-flex align-items-center gap-2">
                                    <i class="bi bi-star-fill text-primary"></i>
                                    <span>Bienvenido</span>
                                </h2>
                                <p class="text-body-secondary mb-4">
                                    OCIANN Vault es una aplicación web open source para gestionar contraseñas y secretos de forma segura.
                                </p>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary btn-lg d-flex align-items-center gap-2">
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Ir al Dashboard</span>
                                        </a>
                                    @else
                                        @if (Route::has('login'))
                                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg d-flex align-items-center gap-2">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Iniciar Sesión</span>
                                            </a>
                                        @else
                                            <span class="btn btn-outline-secondary btn-lg disabled">
                                                Autenticación próximamente
                                            </span>
                                        @endif
                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="btn btn-link btn-lg text-decoration-none d-flex align-items-center gap-2">
                                                <i class="bi bi-person-plus"></i>
                                                <span>Crear Cuenta</span>
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <h3 class="h5 fw-light mb-2">Seguro</h3>
                                    <p class="text-body-secondary small">Tus secretos están cifrados y protegidos</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-people-fill text-info" style="font-size: 3rem;"></i>
                                    </div>
                                    <h3 class="h5 fw-light mb-2">Compartir</h3>
                                    <p class="text-body-secondary small">Comparte de forma controlada con tu equipo</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-file-earmark-text-fill text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <h3 class="h5 fw-light mb-2">Auditoría</h3>
                                    <p class="text-body-secondary small">Registro completo de accesos y cambios</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Minimalista -->
        <footer class="py-4 border-top">
            <div class="container">
                <div class="text-center text-body-secondary small">
                    <p class="mb-0">OCIANN Vault - Open Source Password Manager</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
