<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-warning"></i>
                <span>Auditoría</span>
            </h2>
            <a href="{{ route(request()->routeIs('admin.*') ? 'admin.audit.export' : 'audit.export', request()->query()) }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-download"></i>
                <span>Exportar CSV</span>
            </a>
        </div>
    </x-slot>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Total de Eventos</h6>
                            <h3 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="bi bi-activity fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Por Acción</h6>
                            <h3 class="mb-0">{{ count($stats['by_action'] ?? []) }}</h3>
                        </div>
                        <div class="text-success opacity-25">
                            <i class="bi bi-list-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Tipos de Modelos</h6>
                            <h3 class="mb-0">{{ count($stats['by_model_type'] ?? []) }}</h3>
                        </div>
                        <div class="text-info opacity-25">
                            <i class="bi bi-diagram-3 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-body border-0 pb-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-funnel text-body-secondary"></i> Filtros
            </h6>
        </div>
        <div class="card-body pt-3">
            <form method="GET" action="{{ route(request()->routeIs('admin.*') ? 'admin.audit.index' : 'audit.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label small fw-semibold">Usuario</label>
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="action" class="form-label small fw-semibold">Acción</label>
                    <select name="action" id="action" class="form-select form-select-sm">
                        <option value="">Todas las acciones</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['action'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="model_type" class="form-label small fw-semibold">Tipo de Modelo</label>
                    <select name="model_type" id="model_type" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($modelTypes as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['model_type'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label small fw-semibold">Fecha Inicio</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label small fw-semibold">Fecha Fin</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route(request()->routeIs('admin.*') ? 'admin.audit.index' : 'audit.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4" style="width: 60px;">ID</th>
                                <th style="width: 140px;">Fecha</th>
                                <th style="width: 200px;">Usuario</th>
                                <th style="width: 120px;">Acción</th>
                                <th style="width: 140px;">Tipo</th>
                                <th style="width: 100px;">ID Modelo</th>
                                <th style="width: 140px;">IP</th>
                                <th class="text-end pe-4" style="width: 80px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-body-secondary small">#{{ $log->id }}</span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium">{{ $log->created_at->format('d/m/Y') }}</div>
                                            <div class="text-body-secondary">{{ $log->created_at->format('H:i:s') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <div class="d-flex align-items-center gap-2">
                                                @if($log->user->avatar)
                                                    <img src="{{ $log->user->getAvatarUrl() }}" alt="{{ $log->user->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-semibold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                        {{ $log->user->getInitials() }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="small fw-medium">{{ $log->user->name }}</div>
                                                    <div class="text-body-secondary" style="font-size: 0.7rem;">{{ $log->user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-body-secondary small">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $actionColors = [
                                                'create' => 'success',
                                                'update' => 'primary',
                                                'delete' => 'danger',
                                                'view' => 'info',
                                                'share' => 'warning',
                                                'unshare' => 'secondary',
                                            ];
                                            $color = $actionColors[$log->action] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $actions[$log->action] ?? $log->action }}</span>
                                    </td>
                                    <td>
                                        <span class="text-body-secondary small">
                                            {{ $modelTypes[$log->model_type] ?? class_basename($log->model_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->model_id)
                                            <code class="small bg-body-secondary px-2 py-1 rounded">{{ $log->model_id }}</code>
                                        @else
                                            <span class="text-body-secondary small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-body-secondary small font-monospace">{{ $log->ip_address ?? '-' }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#logDetailModal{{ $log->id }}" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de Detalle -->
                                <div class="modal fade" id="logDetailModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-light">Detalle del Log #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body pt-0">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4 text-body-secondary small">ID:</dt>
                                                    <dd class="col-sm-8 mb-2">{{ $log->id }}</dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">Fecha:</dt>
                                                    <dd class="col-sm-8 mb-2">{{ $log->created_at->format('d/m/Y H:i:s') }}</dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">Usuario:</dt>
                                                    <dd class="col-sm-8 mb-2">{{ $log->user?->name ?? 'Sistema' }}</dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">Acción:</dt>
                                                    <dd class="col-sm-8 mb-2">
                                                        <span class="badge bg-{{ $color }}">{{ $actions[$log->action] ?? $log->action }}</span>
                                                    </dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">Tipo de Modelo:</dt>
                                                    <dd class="col-sm-8 mb-2">{{ $modelTypes[$log->model_type] ?? class_basename($log->model_type) }}</dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">ID del Modelo:</dt>
                                                    <dd class="col-sm-8 mb-2">{{ $log->model_id ?? '-' }}</dd>
                                                    
                                                    <dt class="col-sm-4 text-body-secondary small">IP:</dt>
                                                    <dd class="col-sm-8 mb-2"><code class="small">{{ $log->ip_address ?? '-' }}</code></dd>
                                                    
                                                    @if($log->user_agent)
                                                        <dt class="col-sm-4 text-body-secondary small">User Agent:</dt>
                                                        <dd class="col-sm-8 mb-2"><small class="text-body-secondary">{{ $log->user_agent }}</small></dd>
                                                    @endif
                                                    
                                                    @if($log->changes)
                                                        <dt class="col-sm-4 text-body-secondary small">Cambios:</dt>
                                                        <dd class="col-sm-8 mb-2">
                                                            <pre class="bg-body-secondary p-3 rounded small mb-0" style="max-height: 200px; overflow-y: auto; font-size: 0.8rem;">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </dd>
                                                    @endif
                                                    
                                                    @if($log->meta)
                                                        <dt class="col-sm-4 text-body-secondary small">Meta:</dt>
                                                        <dd class="col-sm-8 mb-2">
                                                            <pre class="bg-body-secondary p-3 rounded small mb-0" style="max-height: 200px; overflow-y: auto; font-size: 0.8rem;">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </dd>
                                                    @endif
                                                </dl>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0 pt-3">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-check text-body-secondary opacity-50" style="font-size: 4rem;"></i>
                    <p class="text-body-secondary mt-3 mb-0">No se encontraron logs de auditoría</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
