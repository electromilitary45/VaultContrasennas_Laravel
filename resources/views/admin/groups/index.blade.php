<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-diagram-3 text-primary"></i>
                <span>Gestión de Grupos</span>
            </h2>
        </div>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Total Grupos</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-diagram-3"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Total Miembros</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_members']) }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-people"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Grupos con Items</h6>
                            <h3 class="mb-0">{{ number_format($stats['groups_with_items']) }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.groups.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label small">Búsqueda</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Nombre, descripción o propietario..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="owner_id" class="form-label small">Propietario</label>
                    <select name="owner_id" id="owner_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ ($filters['owner_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_by" class="form-label small">Ordenar por</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'selected' : '' }}>Fecha</option>
                        <option value="name" {{ ($filters['sort_by'] ?? '') === 'name' ? 'selected' : '' }}>Nombre</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="sort_dir" class="form-label small">Dir.</label>
                    <select name="sort_dir" id="sort_dir" class="form-select">
                        <option value="desc" {{ ($filters['sort_dir'] ?? 'desc') === 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ ($filters['sort_dir'] ?? '') === 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Grupos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-list-ul"></i> Grupos ({{ $groups->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($groups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Nombre</th>
                                <th>Descripción</th>
                                <th>Propietario</th>
                                <th>Miembros</th>
                                <th>Fecha</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td class="ps-4">
                                        <strong>{{ $group->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-body-secondary">
                                            {{ Str::limit($group->description ?? 'Sin descripción', 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($group->owner->avatar)
                                                <img src="{{ $group->owner->getAvatarUrl() }}" 
                                                     alt="{{ $group->owner->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                    {{ $group->owner->getInitials() }}
                                                </div>
                                            @endif
                                            <span>{{ $group->owner->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-people"></i> {{ $group->active_members_count ?? $group->activeMembers()->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $group->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0">
                    {{ $groups->links() }}
                </div>
            @else
                <div class="text-center text-body-secondary py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No se encontraron grupos</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
