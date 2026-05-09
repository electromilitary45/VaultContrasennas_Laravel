<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-shield-lock text-primary"></i>
                <span>Vault Items</span>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary mb-1 small">Total Items</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem; opacity: 0.2;">
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
                            <h6 class="text-body-secondary mb-1 small">Favoritos</h6>
                            <h3 class="mb-0">{{ number_format($stats['favorites']) }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-star-fill"></i>
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
                            <h6 class="text-body-secondary mb-1 small">Con TOTP</h6>
                            <h3 class="mb-0">{{ number_format($stats['with_totp']) }}</h3>
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
                            <h6 class="text-body-secondary mb-1 small">Por Tipo</h6>
                            <h3 class="mb-0">{{ number_format(array_sum($stats['by_type'])) }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-tags"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vault.index') }}" class="row g-3">
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
                               placeholder="Título o propietario..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label small">Tipo</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Todos</option>
                        <option value="auth" {{ ($filters['type'] ?? '') === 'auth' ? 'selected' : '' }}>Auth</option>
                        <option value="card" {{ ($filters['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="note" {{ ($filters['type'] ?? '') === 'note' ? 'selected' : '' }}>Note</option>
                        <option value="api_key" {{ ($filters['type'] ?? '') === 'api_key' ? 'selected' : '' }}>API Key</option>
                        <option value="ssh_key" {{ ($filters['type'] ?? '') === 'ssh_key' ? 'selected' : '' }}>SSH Key</option>
                        <option value="env_file" {{ ($filters['type'] ?? '') === 'env_file' ? 'selected' : '' }}>Archivo .env</option>
                    </select>
                </div>
                <div class="col-md-3">
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
                        <option value="title" {{ ($filters['sort_by'] ?? '') === 'title' ? 'selected' : '' }}>Título</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="sort_dir" class="form-label small">Dirección</label>
                    <select name="sort_dir" id="sort_dir" class="form-select">
                        <option value="desc" {{ ($filters['sort_dir'] ?? 'desc') === 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ ($filters['sort_dir'] ?? '') === 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.vault.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Items -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-list-ul"></i> Items del Vault ({{ $items->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Título</th>
                                <th>Tipo</th>
                                <th>Propietario</th>
                                <th>Carpeta</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->type === 'auth')
                                                <i class="bi bi-key text-primary"></i>
                                            @elseif($item->type === 'card')
                                                <i class="bi bi-credit-card text-info"></i>
                                            @elseif($item->type === 'note')
                                                <i class="bi bi-sticky text-warning"></i>
                                            @elseif($item->type === 'api_key')
                                                <i class="bi bi-code text-success"></i>
                                            @elseif($item->type === 'ssh_key')
                                                <i class="bi bi-terminal text-secondary"></i>
                                            @elseif($item->type === 'env_file')
                                                <i class="bi bi-file-earmark-code text-info"></i>
                                            @endif
                                            <strong>{{ $item->title }}</strong>
                                            @if($item->favorite)
                                                <i class="bi bi-star-fill text-warning" title="Favorito"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($item->type) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->owner->avatar)
                                                <img src="{{ $item->owner->getAvatarUrl() }}" 
                                                     alt="{{ $item->owner->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                    {{ $item->owner->getInitials() }}
                                                </div>
                                            @endif
                                            <span>{{ $item->owner->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->folder)
                                            <span class="text-body-secondary">
                                                <i class="bi bi-folder"></i> {{ $item->folder->name }}
                                            </span>
                                        @else
                                            <span class="text-body-secondary small">Sin carpeta</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status === 'active')
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $item->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('admin.vault.show', $item) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
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
                    {{ $items->links() }}
                </div>
            @else
                <div class="text-center text-body-secondary py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No se encontraron items</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
