<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.vault.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver a lista">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-shield-lock text-primary"></i>
                <span>Detalle de Item</span>
            </h2>
            <form id="form-delete-vault-admin-{{ $item->id }}" method="POST" action="{{ route('admin.vault.destroy', $item) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modal-delete-vault-admin-{{ $item->id }}">
                    <i class="bi bi-trash"></i>
                    <span class="d-none d-sm-inline">Eliminar</span>
                </button>
            </form>
            <x-confirm-modal
                id="modal-delete-vault-admin-{{ $item->id }}"
                form-id="form-delete-vault-admin-{{ $item->id }}"
                title="Eliminar item"
                message="¿Eliminar este item? Esta acción no se puede deshacer."
                confirm-text="Eliminar"
                confirm-class="btn-danger"
            />
        </div>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Información del Item -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8"><code>{{ $item->id }}</code></dd>
                        
                        <dt class="col-sm-4">Título:</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $item->title }}</strong>
                            @if($item->favorite)
                                <i class="bi bi-star-fill text-warning ms-2" title="Favorito"></i>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Tipo:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">{{ ucfirst($item->type) }}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Propietario:</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                @if($item->owner->avatar)
                                    <img src="{{ $item->owner->getAvatarUrl() }}" 
                                         alt="{{ $item->owner->name }}" 
                                         class="rounded-circle" 
                                         style="width: 32px; height: 32px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem;">
                                        {{ $item->owner->getInitials() }}
                                    </div>
                                @endif
                                <div>
                                    <div>{{ $item->owner->name }}</div>
                                    <small class="text-body-secondary">{{ $item->owner->email }}</small>
                                </div>
                            </div>
                        </dd>
                        
                        <dt class="col-sm-4">Carpeta:</dt>
                        <dd class="col-sm-8">
                            @if($item->folder)
                                <span class="text-body-secondary">
                                    <i class="bi bi-folder"></i> {{ $item->folder->name }}
                                </span>
                            @else
                                <span class="text-body-secondary small">Sin carpeta</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($item->status === 'active')
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Creado:</dt>
                        <dd class="col-sm-8">
                            {{ $item->created_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $item->created_at->diffForHumans() }}
                            </small>
                        </dd>
                        
                        <dt class="col-sm-4">Última actualización:</dt>
                        <dd class="col-sm-8">
                            {{ $item->updated_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $item->updated_at->diffForHumans() }}
                            </small>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-bar-chart"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-body-secondary">Compartido con usuarios:</span>
                        <strong>{{ $stats['shares_users'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-body-secondary">Compartido con grupos:</span>
                        <strong>{{ $stats['shares_groups'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Versiones:</span>
                        <strong>{{ $stats['versions'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparticiones con Usuarios -->
    @if($userShares->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-body border-0">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-person"></i> Compartido con Usuarios ({{ $userShares->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Usuario</th>
                                <th>Permiso</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userShares as $share)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($share->user->avatar)
                                                <img src="{{ $share->user->getAvatarUrl() }}" 
                                                     alt="{{ $share->user->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                    {{ $share->user->getInitials() }}
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $share->user->name }}</div>
                                                <small class="text-body-secondary">{{ $share->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $share->permission === 'view' ? 'info' : ($share->permission === 'edit' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($share->permission) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $share->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Comparticiones con Grupos -->
    @if($groupShares->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body border-0">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-diagram-3"></i> Compartido con Grupos ({{ $groupShares->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Grupo</th>
                                <th>Permiso</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupShares as $share)
                                <tr>
                                    <td class="ps-4">
                                        <div>
                                            <strong>{{ $share->group->name }}</strong>
                                            @if($share->group->description)
                                                <small class="text-body-secondary d-block">{{ $share->group->description }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $share->permission === 'view' ? 'info' : ($share->permission === 'edit' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($share->permission) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $share->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($userShares->count() === 0 && $groupShares->count() === 0)
        <div class="alert alert-info border-0">
            <i class="bi bi-info-circle"></i>
            Este item no está compartido con ningún usuario o grupo.
        </div>
    @endif
</x-admin-layout>
