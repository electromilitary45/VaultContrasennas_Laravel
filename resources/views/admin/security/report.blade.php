<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-clipboard-data text-primary"></i>
                <span>Reporte General de Seguridad</span>
            </h2>
        </div>
    </x-slot>

    <!-- Métricas principales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Total Usuarios</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                            <small class="text-body-secondary">{{ number_format($stats['active_users']) }} activos</small>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Con 2FA</h6>
                            <h3 class="mb-0">{{ number_format($stats['users_with_2fa']) }}</h3>
                            <small class="text-body-secondary">{{ $stats['total_users'] > 0 ? number_format(($stats['users_with_2fa'] / $stats['total_users']) * 100, 1) : 0 }}% del total</small>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Contraseñas Débiles</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($stats['weak_passwords_count']) }}</h3>
                            <small class="text-body-secondary">de {{ number_format($stats['total_auth_items']) }} items</small>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Contraseñas Reutilizadas</h6>
                            <h3 class="mb-0 text-warning">{{ number_format($stats['reused_passwords_count']) }}</h3>
                            <small class="text-body-secondary">patrones detectados</small>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuarios inactivos -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-2">Inactivos 30 días</h6>
                    <h3 class="mb-0 text-info">{{ number_format($stats['inactive_30_days']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-2">Inactivos 90 días</h6>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['inactive_90_days']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-2">Inactivos 180 días</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['inactive_180_days']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview de problemas -->
    <div class="row g-3">
        @if(count($weakPasswords) > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-light">
                            <i class="bi bi-shield-x text-danger"></i> Contraseñas Débiles (Preview)
                        </h6>
                        <a href="{{ route('admin.security.weak-passwords') }}" class="btn btn-sm btn-outline-danger">
                            Ver todas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($weakPasswords as $item)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $item['item_title'] }}</div>
                                            <small class="text-body-secondary">{{ $item['owner_name'] }}</small>
                                        </div>
                                        <div>
                                            @foreach(array_slice($item['issues'], 0, 2) as $issue)
                                                <span class="badge bg-danger-subtle text-danger mb-1 d-block">{{ $issue }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(count($reusedPasswords) > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-light">
                            <i class="bi bi-arrow-repeat text-warning"></i> Contraseñas Reutilizadas (Preview)
                        </h6>
                        <a href="{{ route('admin.security.reused-passwords') }}" class="btn btn-sm btn-outline-warning">
                            Ver todas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($reusedPasswords as $reused)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">Hash: <code class="small">{{ $reused['password_hash'] }}</code></div>
                                            <small class="text-body-secondary">{{ $reused['reuse_count'] }} items afectados</small>
                                        </div>
                                        <span class="badge bg-warning text-dark">{{ $reused['reuse_count'] }}x</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
