<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i>
                <span>Grupos</span>
            </h2>
            <a href="{{ route('groups.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo Grupo</span>
            </a>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4 border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'groups' ? 'active' : '' }}" 
                            id="groups-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#groups" 
                            type="button" 
                            role="tab">
                        <i class="bi bi-people"></i> Mis Grupos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'invitations' ? 'active' : '' }}" 
                            id="invitations-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#invitations" 
                            type="button" 
                            role="tab">
                        <i class="bi bi-envelope"></i> Invitaciones Pendientes
                        @if($pendingInvitations->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingInvitations->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'available' ? 'active' : '' }}" 
                            id="available-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#available" 
                            type="button" 
                            role="tab">
                        <i class="bi bi-search"></i> Grupos Disponibles
                    </button>
                </li>
                @if($pendingAccessRequests->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}" 
                                id="requests-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#requests" 
                                type="button" 
                                role="tab">
                            <i class="bi bi-person-plus"></i> Solicitudes
                            <span class="badge bg-danger ms-1">{{ $pendingAccessRequests->count() }}</span>
                        </button>
                    </li>
                @endif
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab: Mis Grupos -->
                <div class="tab-pane fade {{ $activeTab === 'groups' ? 'show active' : '' }}" 
                     id="groups" 
                     role="tabpanel">
                    <!-- Grupos Propios -->
                    @if($ownedGroups->count() > 0)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-body border-0">
                                <h5 class="mb-0 fw-light">
                                    <i class="bi bi-shield-check text-primary"></i> Grupos que Administro
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-0 ps-4">Nombre</th>
                                                <th class="border-0">Descripción</th>
                                                <th class="border-0">Miembros</th>
                                                <th class="border-0">Creado</th>
                                                <th class="border-0 text-end pe-4" style="width: 200px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ownedGroups as $group)
                                                <tr>
                                                    <td class="ps-4">
                                                        <a href="{{ route('groups.items.index', $group) }}" class="text-decoration-none text-body">
                                                            <strong>{{ $group->name }}</strong>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="text-body-secondary">
                                                            {{ Str::limit($group->description ?? 'Sin descripción', 50) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-people"></i> {{ $group->active_members_count }}
                                                        </span>
                                                    </td>
                                                    <td class="text-body-secondary small">
                                                        {{ $group->created_at->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            <a href="{{ route('groups.items.index', $group) }}" class="btn btn-sm btn-primary" title="Ver Items">
                                                                <i class="bi bi-shield-lock"></i> Items
                                                            </a>
                                                            <a href="{{ route('groups.admin', $group) }}" class="btn btn-sm btn-outline-primary" title="Administrar">
                                                                <i class="bi bi-gear"></i> Admin
                                                            </a>
                                                            <a href="{{ route('groups.edit', $group) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form id="form-delete-group-{{ $group->id }}" method="POST" action="{{ route('groups.destroy', $group) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#modal-delete-group-{{ $group->id }}">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                            <x-confirm-modal
                                                                id="modal-delete-group-{{ $group->id }}"
                                                                form-id="form-delete-group-{{ $group->id }}"
                                                                title="Eliminar grupo"
                                                                message="¿Estás seguro de que deseas eliminar este grupo? Esta acción no se puede deshacer."
                                                                confirm-text="Eliminar"
                                                                confirm-class="btn-danger"
                                                            />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Grupos donde soy miembro -->
                    @if($memberGroups->count() > 0)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-body border-0">
                                <h5 class="mb-0 fw-light">
                                    <i class="bi bi-person-check text-info"></i> Grupos donde soy Miembro
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-0 ps-4">Nombre</th>
                                                <th class="border-0">Descripción</th>
                                                <th class="border-0">Propietario</th>
                                                <th class="border-0">Mi Rol</th>
                                                <th class="border-0">Miembros</th>
                                                <th class="border-0 text-end pe-4" style="width: 150px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($memberGroups as $group)
                                                @php
                                                    $userRole = $group->getUserRole(Auth::user());
                                                @endphp
                                                <tr>
                                                    <td class="ps-4">
                                                        <a href="{{ route('groups.items.index', $group) }}" class="text-decoration-none text-body">
                                                            <strong>{{ $group->name }}</strong>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="text-body-secondary">
                                                            {{ Str::limit($group->description ?? 'Sin descripción', 50) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-body-secondary">{{ $group->owner->name }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $userRole === 'admin' ? 'warning' : ($userRole === 'member' ? 'info' : 'secondary') }}">
                                                            {{ ucfirst($userRole) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-people"></i> {{ $group->active_members_count }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            <a href="{{ route('groups.items.index', $group) }}" class="btn btn-sm btn-primary" title="Ver Items">
                                                                <i class="bi bi-shield-lock"></i> Items
                                                            </a>
                                                            @if(in_array($userRole, ['owner', 'admin']))
                                                                <a href="{{ route('groups.admin', $group) }}" class="btn btn-sm btn-outline-primary" title="Administrar">
                                                                    <i class="bi bi-gear"></i> Admin
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Mensaje cuando no hay grupos -->
                    @if($ownedGroups->isEmpty() && $memberGroups->isEmpty())
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-people text-body-secondary" style="font-size: 4rem;"></i>
                                <h4 class="mt-4 fw-light">No tienes grupos</h4>
                                <p class="text-body-secondary">Crea tu primer grupo para compartir items del vault con otros usuarios.</p>
                                <a href="{{ route('groups.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Crear Primer Grupo
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tab: Invitaciones Pendientes -->
                <div class="tab-pane fade {{ $activeTab === 'invitations' ? 'show active' : '' }}" 
                     id="invitations" 
                     role="tabpanel">
                    @if($pendingInvitations->isEmpty())
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-envelope-check text-body-secondary" style="font-size: 4rem;"></i>
                                <h4 class="mt-4 fw-light">No hay invitaciones pendientes</h4>
                                <p class="text-body-secondary">Cuando recibas una invitación a un grupo, aparecerá aquí.</p>
                            </div>
                        </div>
                    @else
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-body border-0">
                                <h5 class="mb-0 fw-light">
                                    <i class="bi bi-envelope text-primary"></i> Invitaciones Pendientes
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach($pendingInvitations as $invitation)
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $invitation->group->name }}</h6>
                                                    <p class="text-body-secondary mb-1 small">
                                                        {{ $invitation->group->description ?? 'Sin descripción' }}
                                                    </p>
                                                    <div class="d-flex align-items-center gap-3 small text-body-secondary">
                                                        <span>
                                                            <i class="bi bi-person"></i> Invitado por: {{ $invitation->invitedBy->name ?? 'N/A' }}
                                                        </span>
                                                        <span>
                                                            <i class="bi bi-calendar"></i> {{ $invitation->created_at->diffForHumans() }}
                                                        </span>
                                                        <span>
                                                            <i class="bi bi-shield"></i> Rol: {{ ucfirst($invitation->role) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <form id="form-accept-invitation-{{ $invitation->id }}" 
                                                          method="POST" 
                                                          action="{{ route('groups.invitations.accept', $invitation->group) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modal-accept-invitation-{{ $invitation->id }}">
                                                            <i class="bi bi-check-circle"></i> Aceptar
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-accept-invitation-{{ $invitation->id }}"
                                                        form-id="form-accept-invitation-{{ $invitation->id }}"
                                                        title="Aceptar invitación"
                                                        message="¿Aceptar la invitación al grupo &quot;{{ $invitation->group->name }}&quot;?"
                                                        confirm-text="Aceptar"
                                                        confirm-class="btn-success"
                                                    />
                                                    <form id="form-reject-invitation-{{ $invitation->id }}" 
                                                          method="POST" 
                                                          action="{{ route('groups.invitations.reject', $invitation->group) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modal-reject-invitation-{{ $invitation->id }}">
                                                            <i class="bi bi-x-circle"></i> Rechazar
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-reject-invitation-{{ $invitation->id }}"
                                                        form-id="form-reject-invitation-{{ $invitation->id }}"
                                                        title="Rechazar invitación"
                                                        message="¿Rechazar la invitación al grupo &quot;{{ $invitation->group->name }}&quot;?"
                                                        confirm-text="Rechazar"
                                                        confirm-class="btn-danger"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tab: Grupos Disponibles -->
                <div class="tab-pane fade {{ $activeTab === 'available' ? 'show active' : '' }}" 
                     id="available" 
                     role="tabpanel">
                    @if($availableGroups->isEmpty())
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-search text-body-secondary" style="font-size: 4rem;"></i>
                                <h4 class="mt-4 fw-light">No hay grupos disponibles</h4>
                                <p class="text-body-secondary">Ya eres miembro de todos los grupos de tu organización o no hay grupos disponibles.</p>
                            </div>
                        </div>
                    @else
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-body border-0">
                                <h5 class="mb-0 fw-light">
                                    <i class="bi bi-search text-primary"></i> Grupos Disponibles
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-0 ps-4">Nombre</th>
                                                <th class="border-0">Descripción</th>
                                                <th class="border-0">Propietario</th>
                                                <th class="border-0">Miembros</th>
                                                <th class="border-0 text-end pe-4" style="width: 200px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($availableGroups as $group)
                                                @php
                                                    $hasPendingRequest = $accessRequestService->hasPendingRequest($group, Auth::user());
                                                    $rejectedRequest = \App\Models\GroupAccessRequest::where('group_id', $group->id)
                                                        ->where('user_id', Auth::id())
                                                        ->where('status', 'rejected')
                                                        ->latest()
                                                        ->first();
                                                @endphp
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
                                                        <span class="text-body-secondary">{{ $group->owner->name }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-people"></i> {{ $group->active_members_count }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($hasPendingRequest)
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-clock"></i> Solicitud Pendiente
                                                            </span>
                                                        @elseif($rejectedRequest)
                                                            <form id="form-request-access-{{ $group->id }}" 
                                                                  method="POST" 
                                                                  action="{{ route('groups.access-requests.store', $group) }}" 
                                                                  class="d-inline">
                                                                @csrf
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-primary" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modal-request-access-{{ $group->id }}">
                                                                    <i class="bi bi-person-plus"></i> Volver a Solicitar
                                                                </button>
                                                            </form>
                                                            <x-confirm-modal
                                                                id="modal-request-access-{{ $group->id }}"
                                                                form-id="form-request-access-{{ $group->id }}"
                                                                title="Solicitar acceso"
                                                                message="¿Solicitar acceso al grupo &quot;{{ $group->name }}&quot;?"
                                                                confirm-text="Solicitar"
                                                                confirm-class="btn-primary"
                                                            />
                                                        @else
                                                            <form id="form-request-access-{{ $group->id }}" 
                                                                  method="POST" 
                                                                  action="{{ route('groups.access-requests.store', $group) }}" 
                                                                  class="d-inline">
                                                                @csrf
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-primary" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modal-request-access-{{ $group->id }}">
                                                                    <i class="bi bi-person-plus"></i> Solicitar Acceso
                                                                </button>
                                                            </form>
                                                            <x-confirm-modal
                                                                id="modal-request-access-{{ $group->id }}"
                                                                form-id="form-request-access-{{ $group->id }}"
                                                                title="Solicitar acceso"
                                                                message="¿Solicitar acceso al grupo &quot;{{ $group->name }}&quot;?"
                                                                confirm-text="Solicitar"
                                                                confirm-class="btn-primary"
                                                            />
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tab: Solicitudes Pendientes (para owners/admins) -->
                @if($pendingAccessRequests->count() > 0)
                    <div class="tab-pane fade {{ $activeTab === 'requests' ? 'show active' : '' }}" 
                         id="requests" 
                         role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-body border-0">
                                <h5 class="mb-0 fw-light">
                                    <i class="bi bi-person-plus text-primary"></i> Solicitudes Pendientes
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach($pendingAccessRequests as $accessRequest)
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $accessRequest->group->name }}</h6>
                                                    <p class="text-body-secondary mb-1 small">
                                                        <i class="bi bi-person"></i> {{ $accessRequest->user->name }} solicita acceso
                                                    </p>
                                                    @if($accessRequest->message)
                                                        <p class="text-body-secondary mb-1 small">
                                                            <i class="bi bi-chat-left-text"></i> {{ $accessRequest->message }}
                                                        </p>
                                                    @endif
                                                    <div class="d-flex align-items-center gap-3 small text-body-secondary">
                                                        <span>
                                                            <i class="bi bi-calendar"></i> {{ $accessRequest->requested_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <form id="form-accept-request-{{ $accessRequest->id }}" 
                                                          method="POST" 
                                                          action="{{ route('groups.access-requests.accept', [$accessRequest->group, $accessRequest]) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modal-accept-request-{{ $accessRequest->id }}">
                                                            <i class="bi bi-check-circle"></i> Aceptar
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-accept-request-{{ $accessRequest->id }}"
                                                        form-id="form-accept-request-{{ $accessRequest->id }}"
                                                        title="Aceptar solicitud"
                                                        message="¿Aceptar la solicitud de acceso de {{ $accessRequest->user->name }} al grupo &quot;{{ $accessRequest->group->name }}&quot;?"
                                                        confirm-text="Aceptar"
                                                        confirm-class="btn-success"
                                                    />
                                                    <form id="form-reject-request-{{ $accessRequest->id }}" 
                                                          method="POST" 
                                                          action="{{ route('groups.access-requests.reject', [$accessRequest->group, $accessRequest]) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modal-reject-request-{{ $accessRequest->id }}">
                                                            <i class="bi bi-x-circle"></i> Rechazar
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-reject-request-{{ $accessRequest->id }}"
                                                        form-id="form-reject-request-{{ $accessRequest->id }}"
                                                        title="Rechazar solicitud"
                                                        message="¿Rechazar la solicitud de acceso de {{ $accessRequest->user->name }} al grupo &quot;{{ $accessRequest->group->name }}&quot;?"
                                                        confirm-text="Rechazar"
                                                        confirm-class="btn-danger"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
