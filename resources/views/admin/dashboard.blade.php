<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2 text-primary"></i>
            <span>Dashboard de Administración</span>
        </h2>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <!-- Métricas principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Usuarios Activos</h6>
                            <h3 class="mb-0">{{ number_format($metrics['users']['active']) }}</h3>
                            <small class="text-body-secondary">de {{ number_format($metrics['users']['total']) }} total</small>
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
                            <h6 class="text-body-secondary mb-1 small">Items del Vault</h6>
                            <h3 class="mb-0">{{ number_format($metrics['vault']['total']) }}</h3>
                            <small class="text-body-secondary">{{ number_format($metrics['vault']['favorites']) }} favoritos</small>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-lock"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Grupos</h6>
                            <h3 class="mb-0">{{ number_format($metrics['groups']['total']) }}</h3>
                            <small class="text-body-secondary">{{ number_format($metrics['groups']['total_members']) }} miembros</small>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-diagram-3"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Comparticiones</h6>
                            <h3 class="mb-0">{{ number_format($metrics['sharing']['user_shares'] + $metrics['sharing']['group_shares']) }}</h3>
                            <small class="text-body-secondary">{{ number_format($metrics['sharing']['user_shares']) }} usuarios, {{ number_format($metrics['sharing']['group_shares']) }} grupos</small>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-share"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($metrics['invitation_codes']) && count($metrics['invitation_codes']) > 0)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Códigos de invitación</h6>
                            <h3 class="mb-0">{{ number_format($metrics['invitation_codes']['unused']) }}</h3>
                            <small class="text-body-secondary">sin usar de {{ number_format($metrics['invitation_codes']['total']) }} total</small>
                        </div>
                        <div class="text-secondary" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.invitation-codes.index') }}" class="btn btn-sm btn-outline-secondary mt-2 w-100">
                        <i class="bi bi-ticket-perforated"></i> Gestionar códigos
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Información del sistema y Accesos rápidos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-info-circle"></i> Información del Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Versión de Laravel:</dt>
                        <dd class="col-sm-7"><code>{{ app()->version() }}</code></dd>
                        
                        <dt class="col-sm-5">Versión de PHP:</dt>
                        <dd class="col-sm-7"><code>{{ PHP_VERSION }}</code></dd>
                        
                        <dt class="col-sm-5">Entorno:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ app()->environment() === 'production' ? 'success' : 'warning' }}">
                                {{ app()->environment() }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-5">Usuarios Verificados:</dt>
                        <dd class="col-sm-7">{{ number_format($metrics['users']['verified']) }}</dd>
                        
                        <dt class="col-sm-5">Con 2FA:</dt>
                        <dd class="col-sm-7">{{ number_format($metrics['users']['with_2fa']) }}</dd>
                        
                        <dt class="col-sm-5">Admins:</dt>
                        <dd class="col-sm-7">
                            {{ number_format($metrics['users']['admins']) }}
                            ({{ number_format($metrics['users']['super']) }} super)
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-lightning-charge"></i> Accesos Rápidos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-people"></i> Gestionar Usuarios</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.vault.index') }}" class="btn btn-outline-success d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-shield-lock"></i> Ver Vault Items</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-info d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-diagram-3"></i> Gestionar Grupos</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-warning d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-clipboard-check"></i> Ver Auditoría</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        @if(Auth::user()->isPlatformSuperAdmin())
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-gear"></i> Configuración del Sistema</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <form id="form-publish-extension" method="POST" action="{{ route('admin.extension.publish-version') }}" class="d-inline">
                                @csrf
                            </form>
                            <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-between" data-bs-toggle="modal" data-bs-target="#modal-publish-extension">
                                <span><i class="bi bi-puzzle"></i> Publicar nueva versión de la extensión</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                            <x-confirm-modal
                                id="modal-publish-extension"
                                formId="form-publish-extension"
                                title="Publicar nueva versión"
                                message="Se actualizará el timestamp de la extensión. Los usuarios que comprueben actualizaciones desde el popup verán que hay una nueva versión disponible. ¿Continuar?"
                                confirm-text="Publicar"
                                confirm-class="btn-primary"
                            />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad últimos 30 días -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-calendar-range"></i> Actividad de los Últimos 30 Días
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-primary">{{ number_format($activityLast30Days['items_created']) }}</div>
                                <small class="text-body-secondary">Items Creados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-success">{{ number_format($activityLast30Days['items_updated']) }}</div>
                                <small class="text-body-secondary">Items Actualizados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-info">{{ number_format($activityLast30Days['groups_created']) }}</div>
                                <small class="text-body-secondary">Grupos Creados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-warning">{{ number_format($activityLast30Days['users_registered']) }}</div>
                                <small class="text-body-secondary">Usuarios Registrados</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-danger">{{ number_format($activityLast30Days['members_invited']) }}</div>
                                <small class="text-body-secondary">Miembros Invitados a Grupos</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-body-secondary rounded">
                                <div class="h4 mb-1 text-secondary">{{ number_format($activityLast30Days['shares_created']) }}</div>
                                <small class="text-body-secondary">Comparticiones Creadas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de actividad diaria -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-bar-chart"></i> Actividad Diaria (Últimos 30 Días)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Eventos recientes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-clock-history"></i> Eventos Recientes (Últimos 7 Días)
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($recentEvents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentEvents as $event)
                                <div class="list-group-item border-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            @if($event['type'] === 'item_created')
                                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-shield-lock"></i>
                                                </div>
                                            @elseif($event['type'] === 'group_created')
                                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-diagram-3"></i>
                                                </div>
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person-plus"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong>{{ $event['description'] }}</strong>
                                            </div>
                                            <small class="text-body-secondary">
                                                <i class="bi bi-clock"></i> {{ $event['timestamp']->diffForHumans() }}
                                                ({{ $event['timestamp']->format('d/m/Y H:i') }})
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-body-secondary py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0">No hay eventos recientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Logs de auditoría recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-clipboard-check"></i> Logs de Auditoría Recientes
                    </h6>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-warning">
                        Ver todos
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentLogs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentLogs as $log)
                                <div class="list-group-item border-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-warning bg-opacity-25 text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                            <i class="bi bi-journal-text"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong>{{ $log->action }}</strong>
                                                <span class="badge bg-light text-dark">{{ $log->model_type }}</span>
                                            </div>
                                            <small class="text-body-secondary">
                                                @if($log->user)
                                                    {{ $log->user->name }} &middot;
                                                @endif
                                                <i class="bi bi-clock"></i> {{ $log->created_at->diffForHumans() }}
                                                ({{ $log->created_at->format('d/m/Y H:i') }})
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-body-secondary py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0">No hay logs recientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('activityChart');
            if (!ctx) return;

            const activityData = @json($dailyActivity);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: activityData.map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
                    }),
                    datasets: [
                        {
                            label: 'Items',
                            data: activityData.map(item => item.items),
                            borderColor: 'rgb(13, 110, 253)',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Grupos',
                            data: activityData.map(item => item.groups),
                            borderColor: 'rgb(13, 202, 240)',
                            backgroundColor: 'rgba(13, 202, 240, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Usuarios',
                            data: activityData.map(item => item.users),
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Comparticiones',
                            data: activityData.map(item => item.shares),
                            borderColor: 'rgb(25, 135, 84)',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>
