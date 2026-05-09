<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i>
                <span>Gestión de Usuarios</span>
            </h2>
            @if(Auth::user()->isOrgAdmin())
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus"></i> Crear usuario
                </a>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Total Usuarios</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
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
                            <h6 class="text-body-secondary mb-1 small">Usuarios Normales</h6>
                            <h3 class="mb-0">{{ number_format($stats['by_role']['user']) }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-person"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Administradores</h6>
                            <h3 class="mb-0">{{ number_format($stats['by_role']['admin'] + $stats['by_role']['super_admin']) }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
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
                            <h6 class="text-body-secondary mb-1 small">Activos</h6>
                            <h3 class="mb-0">{{ number_format($stats['active']) }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($stats['inactive'] > 0)
        <div class="alert alert-warning border-0 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Usuarios inactivos:</strong> {{ number_format($stats['inactive']) }} usuario(s) están desactivados y no pueden iniciar sesión.
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-funnel"></i> Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label small">Búsqueda</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Nombre o email..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="role" class="form-label small">Rol</label>
                    <select name="role" id="role" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="user" {{ ($filters['role'] ?? '') === 'user' ? 'selected' : '' }}>Usuario</option>
                        <option value="admin" {{ ($filters['role'] ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="super_admin" {{ ($filters['role'] ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small">Estado</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_by" class="form-label small">Ordenar por</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'selected' : '' }}>Fecha de registro</option>
                        <option value="name" {{ ($filters['sort_by'] ?? '') === 'name' ? 'selected' : '' }}>Nombre</option>
                        <option value="email" {{ ($filters['sort_by'] ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_dir" class="form-label small">Dirección</label>
                    <select name="sort_dir" id="sort_dir" class="form-select">
                        <option value="desc" {{ ($filters['sort_dir'] ?? 'desc') === 'desc' ? 'selected' : '' }}>Descendente</option>
                        <option value="asc" {{ ($filters['sort_dir'] ?? '') === 'asc' ? 'selected' : '' }}>Ascendente</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 20%;">Usuario</th>
                                <th style="width: 20%;">Email</th>
                                <th style="width: 10%;">Rol</th>
                                <th style="width: 15%;">Estado</th>
                                <th style="width: 15%;">Registro</th>
                                <th style="width: 15%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td><code class="small">{{ $user->id }}</code></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($user->avatar)
                                                <img src="{{ $user->getAvatarUrl() }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                    {{ $user->getInitials() }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-medium">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @if($user->isSuperAdmin())
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($user->isAdmin())
                                            <span class="badge bg-warning">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Usuario</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($user->is_active)
                                                <span class="badge bg-success" style="font-size: 0.7rem;">
                                                    <i class="bi bi-check-circle"></i> Activo
                                                </span>
                                            @else
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                                    <i class="bi bi-x-circle"></i> Inactivo
                                                </span>
                                            @endif
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success" style="font-size: 0.7rem;">
                                                    <i class="bi bi-check-circle"></i> Verificado
                                                </span>
                                            @else
                                                <span class="badge bg-warning" style="font-size: 0.7rem;">
                                                    <i class="bi bi-exclamation-circle"></i> Sin verificar
                                                </span>
                                            @endif
                                            @if($user->hasTwoFactorEnabled())
                                                <span class="badge bg-info" style="font-size: 0.7rem;">
                                                    <i class="bi bi-shield-check"></i> 2FA
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $user->created_at->format('d/m/Y') }}<br>
                                            <span style="font-size: 0.75rem;">{{ $user->created_at->format('H:i') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-people text-body-secondary" style="font-size: 3rem;"></i>
                    <p class="text-body-secondary mt-3 mb-0">No se encontraron usuarios</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
