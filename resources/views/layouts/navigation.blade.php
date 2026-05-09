<nav class="navbar navbar-expand-lg navbar-light bg-body border-bottom shadow-sm">
    <div class="container-fluid px-4">
        <!-- Brand con icono -->
        <a class="navbar-brand fw-light fs-4 d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="bi bi-shield-lock-fill text-primary"></i>
            <span>{{ config('app.name') }}</span>
        </a>

        <!-- Toggle button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('vault.*') ? 'active fw-semibold' : '' }}" href="{{ route('vault.index') }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Vault</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('groups.*') ? 'active fw-semibold' : '' }}" href="{{ route('groups.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Grupos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('integrations.*') ? 'active fw-semibold' : '' }}" href="{{ route('integrations.index') }}">
                        <i class="bi bi-puzzle"></i>
                        <span>Integraciones</span>
                    </a>
                </li>
            </ul>

            <!-- Toggle de tema y User Dropdown -->
            <ul class="navbar-nav">
                <!-- Dropdown de Notificaciones -->
                <x-notifications-dropdown />
                
                <!-- Toggle de tema -->
                <li class="nav-item d-flex align-items-center">
                    <x-theme-toggle />
                </li>

                <!-- User Dropdown con Avatar -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Avatar -->
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->getAvatarUrl() }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="rounded-circle" 
                                 style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem; font-weight: 500;">
                                {{ Auth::user()->getInitials() }}
                            </div>
                        @endif
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="userDropdown" style="min-width: 250px;">
                        <li>
                            <div class="dropdown-header d-flex align-items-center gap-2 px-3 py-2">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->getAvatarUrl() }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1rem; font-weight: 500;">
                                        {{ Auth::user()->getInitials() }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ Auth::user()->name }}</div>
                                    <small class="text-body-secondary d-block">{{ Auth::user()->email }}</small>
                                    @if(Auth::user()->hasTwoFactorEnabled())
                                        <span class="badge bg-success mt-1" style="font-size: 0.65rem;">
                                            <i class="bi bi-shield-check"></i> 2FA
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-gear"></i>
                                <span>Perfil</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.two-factor') }}">
                                <i class="bi bi-shield-check"></i>
                                <span>Autenticación 2FA</span>
                                @if(Auth::user()->hasTwoFactorEnabled())
                                    <span class="badge bg-success ms-auto" style="font-size: 0.65rem;">Activo</span>
                                @endif
                            </a>
                        </li>
                        @if(Auth::user()->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-shield-check text-warning"></i>
                                    <span>Panel de Administración</span>
                                    @if(Auth::user()->isSuperAdmin())
                                        <span class="badge bg-danger ms-auto" style="font-size: 0.65rem;">Super</span>
                                    @else
                                        <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.65rem;">Admin</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Cerrar Sesión</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
