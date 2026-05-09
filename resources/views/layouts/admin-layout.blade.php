<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ theme: 'auto' }" x-init="(() => { if (window.themeManager) { theme = window.themeManager.getStoredTheme(); window.themeManager.applyTheme(theme); } })()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Administración - {{ config('app.name', 'Laravel') }}</title>

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
    <div class="min-vh-100 d-flex">
        <!-- Sidebar de Administración -->
        <aside class="bg-body-secondary border-end" style="width: 250px; min-height: 100vh;">
            <div class="p-3 border-bottom">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-shield-check text-warning fs-4"></i>
                    <h5 class="mb-0 fw-light">Administración</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(Auth::user()->avatar)
                        <img src="{{ Auth::user()->getAvatarUrl() }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="rounded-circle" 
                             style="width: 32px; height: 32px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem;">
                            {{ Auth::user()->getInitials() }}
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">{{ Auth::user()->name }}</div>
                        <div class="small text-body-secondary">
                            @if(Auth::user()->isSuperAdmin())
                                <span class="badge bg-danger" style="font-size: 0.65rem;">Super Admin</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Admin</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="p-2">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    @if(Auth::user()->isPlatformSuperAdmin())
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}" href="{{ route('admin.organizations.index') }}">
                                <i class="bi bi-building"></i>
                                <span>Organizaciones</span>
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->isOrgAdmin())
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.invitation-codes.*') ? 'active' : '' }}" href="{{ route('admin.invitation-codes.index') }}">
                                <i class="bi bi-ticket-perforated"></i>
                                <span>Códigos de invitación</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.vault.*') ? 'active' : '' }}" href="{{ route('admin.vault.index') }}">
                            <i class="bi bi-shield-lock"></i>
                            <span>Vault Items</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}" href="{{ route('admin.groups.index') }}">
                            <i class="bi bi-diagram-3"></i>
                            <span>Grupos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Auditoría</span>
                        </a>
                    </li>
                    @if(Auth::user()->isPlatformSuperAdmin())
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.security.*') ? 'active' : '' }}" href="{{ route('admin.security.index') }}">
                                <i class="bi bi-shield-exclamation"></i>
                                <span>Seguridad</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                <i class="bi bi-gear"></i>
                                <span>Configuración</span>
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider my-2"></li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-danger" href="{{ route('dashboard') }}">
                            <i class="bi bi-arrow-left"></i>
                            <span>Volver a Usuario</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start d-flex align-items-center gap-2 text-body">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex-grow-1 d-flex flex-column">
            <!-- Barra superior -->
            <header class="bg-body border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div class="flex-grow-1 min-w-0">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h2 class="h4 fw-light mb-0">
                                <i class="bi bi-shield-check text-warning"></i>
                                Panel de Administración
                            </h2>
                        @endisset
                    </div>
                    
                    <!-- Toggle de tema -->
                    <div class="flex-shrink-0">
                        <x-theme-toggle />
                    </div>
                </div>
            </header>

            <!-- Contenido -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />
                
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-global-modals />
    @stack('scripts')
</body>
</html>
