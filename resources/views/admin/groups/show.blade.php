<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.groups.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver a lista">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-diagram-3 text-primary"></i>
                <span>Detalle de Grupo</span>
            </h2>
            <form id="form-delete-group-admin-{{ $group->id }}" method="POST" action="{{ route('admin.groups.destroy', $group) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modal-delete-group-admin-{{ $group->id }}">
                    <i class="bi bi-trash"></i>
                    <span class="d-none d-sm-inline">Eliminar</span>
                </button>
            </form>
            <x-confirm-modal
                id="modal-delete-group-admin-{{ $group->id }}"
                form-id="form-delete-group-admin-{{ $group->id }}"
                title="Eliminar grupo"
                message="¿Eliminar este grupo? Esta acción eliminará todos los miembros y comparticiones asociadas. Esta acción no se puede deshacer."
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

    <!-- Información del Grupo -->
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
                        <dd class="col-sm-8"><code>{{ $group->id }}</code></dd>
                        
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8"><strong>{{ $group->name }}</strong></dd>
                        
                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">
                            {{ $group->description ?? 'Sin descripción' }}
                        </dd>
                        
                        <dt class="col-sm-4">Propietario:</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                @if($group->owner->avatar)
                                    <img src="{{ $group->owner->getAvatarUrl() }}" 
                                         alt="{{ $group->owner->name }}" 
                                         class="rounded-circle" 
                                         style="width: 32px; height: 32px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem;">
                                        {{ $group->owner->getInitials() }}
                                    </div>
                                @endif
                                <div>
                                    <div>{{ $group->owner->name }}</div>
                                    <small class="text-body-secondary">{{ $group->owner->email }}</small>
                                </div>
                            </div>
                        </dd>
                        
                        <dt class="col-sm-4">Creado:</dt>
                        <dd class="col-sm-8">
                            {{ $group->created_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $group->created_at->diffForHumans() }}
                            </small>
                        </dd>
                        
                        <dt class="col-sm-4">Última actualización:</dt>
                        <dd class="col-sm-8">
                            {{ $group->updated_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $group->updated_at->diffForHumans() }}
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
                        <span class="text-body-secondary">Total Miembros:</span>
                        <strong>{{ $stats['total_members'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-body-secondary">Items Compartidos:</span>
                        <strong>{{ $stats['total_items'] }}</strong>
                    </div>
                    <hr>
                    <div class="small text-body-secondary mb-2">Por Rol:</div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">Owner:</span>
                        <strong>{{ $stats['by_role']['owner'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">Admin:</span>
                        <strong>{{ $stats['by_role']['admin'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">Member:</span>
                        <strong>{{ $stats['by_role']['member'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Viewer:</span>
                        <strong>{{ $stats['by_role']['viewer'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Miembros del Grupo -->
    @if($members->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body border-0">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-people"></i> Miembros ({{ $members->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha de Ingreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($member->user->avatar)
                                                <img src="{{ $member->user->getAvatarUrl() }}" 
                                                     alt="{{ $member->user->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                    {{ $member->user->getInitials() }}
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $member->user->name }}</div>
                                                <small class="text-body-secondary">{{ $member->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($member->role === 'owner')
                                            <span class="badge bg-danger">Owner</span>
                                        @elseif($member->role === 'admin')
                                            <span class="badge bg-warning">Admin</span>
                                        @elseif($member->role === 'member')
                                            <span class="badge bg-primary">Member</span>
                                        @else
                                            <span class="badge bg-info">Viewer</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->status === 'active')
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($member->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $member->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info border-0">
            <i class="bi bi-info-circle"></i>
            Este grupo no tiene miembros activos.
        </div>
    @endif
</x-admin-layout>
