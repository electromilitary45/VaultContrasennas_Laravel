<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-gear text-primary"></i>
                <span>Administración: {{ $group->name }}</span>
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('groups.items.index', $group) }}" class="btn btn-primary">
                    <i class="bi bi-shield-lock"></i> Ver Items del Vault
                </a>
                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Grupos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
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

            <!-- Información del Grupo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1 fw-light">
                                <i class="bi bi-people text-primary"></i> {{ $group->name }}
                            </h4>
                            @if($group->description)
                                <p class="text-body-secondary mb-0">{{ $group->description }}</p>
                            @endif
                        </div>
                        @if($isOwner)
                            <a href="{{ route('groups.edit', $group) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar Grupo
                            </a>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-primary"></i>
                                <div>
                                    <small class="text-body-secondary d-block">Propietario</small>
                                    <strong>{{ $group->owner->name }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge text-info"></i>
                                <div>
                                    <small class="text-body-secondary d-block">Mi Rol</small>
                                    <span class="badge bg-{{ $userRole === 'owner' ? 'primary' : ($userRole === 'admin' ? 'warning' : ($userRole === 'member' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst($userRole) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar text-body-secondary"></i>
                                <div>
                                    <small class="text-body-secondary d-block">Creado</small>
                                    <span>{{ $group->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Miembros del Grupo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-light">
                        <i class="bi bi-people"></i> Miembros ({{ $members->where('status', 'active')->count() }})
                    </h5>
                    @if(in_array($userRole, ['owner', 'admin']))
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inviteModal">
                            <i class="bi bi-plus-circle"></i> Invitar
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($members->where('status', 'active')->isEmpty())
                        <div class="text-center text-body-secondary py-4">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Este grupo no tiene miembros activos.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0 ps-4">Usuario</th>
                                        <th class="border-0">Rol</th>
                                        <th class="border-0">Invitado por</th>
                                        <th class="border-0">Fecha</th>
                                        @if(in_array($userRole, ['owner', 'admin']))
                                            <th class="border-0 text-end pe-4" style="width: 150px;">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($members->where('status', 'active') as $member)
                                        <tr>
                                            <td class="ps-4">
                                                <div>
                                                    <strong>{{ $member->user->name }}</strong>
                                                    @if($member->user_id === $group->owner_user_id)
                                                        <span class="badge bg-primary ms-2">Owner</span>
                                                    @endif
                                                    <small class="text-body-secondary d-block">{{ $member->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $member->role === 'owner' ? 'primary' : ($member->role === 'admin' ? 'warning' : ($member->role === 'member' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($member->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($member->invitedBy)
                                                    <span class="text-body-secondary">{{ $member->invitedBy->name }}</span>
                                                @else
                                                    <span class="text-body-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-body-secondary small">
                                                {{ $member->created_at->format('d/m/Y') }}
                                            </td>
                                            @if(in_array($userRole, ['owner', 'admin']))
                                                <td class="text-end pe-4">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        @if($isOwner && $member->role !== 'owner')
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                    onclick="openRoleModal({{ $member->id }}, '{{ $member->role }}', '{{ $member->user->name }}')" 
                                                                    title="Cambiar rol">
                                                                <i class="bi bi-person-gear"></i>
                                                            </button>
                                                        @endif
                                                        @if($member->role !== 'owner' && ($isOwner || ($userRole === 'admin' && $member->role !== 'admin')))
                                                            <form id="form-remove-member-{{ $member->id }}" method="POST" action="{{ route('groups.remove-member', $group) }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="member_id" value="{{ $member->id }}">
                                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Remover" data-bs-toggle="modal" data-bs-target="#modal-remove-member-{{ $member->id }}">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            </form>
                                                            <x-confirm-modal
                                                                id="modal-remove-member-{{ $member->id }}"
                                                                form-id="form-remove-member-{{ $member->id }}"
                                                                title="Remover del grupo"
                                                                :message="'¿Estás seguro de que deseas remover a ' . e($member->user->name) . ' del grupo?'"
                                                                confirm-text="Remover"
                                                                confirm-class="btn-danger"
                                                            />
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones (solo para owner) -->
            @if($isOwner)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-light">Acciones del Grupo</h6>
                                <p class="text-body-secondary small mb-0">Gestiona este grupo</p>
                            </div>
                            <form id="form-destroy-group-admin-{{ $group->id }}" method="POST" action="{{ route('groups.destroy', $group) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-destroy-group-admin-{{ $group->id }}">
                                    <i class="bi bi-trash"></i> Eliminar Grupo
                                </button>
                            </form>
                            <x-confirm-modal
                                id="modal-destroy-group-admin-{{ $group->id }}"
                                form-id="form-destroy-group-admin-{{ $group->id }}"
                                title="Eliminar grupo"
                                message="¿Estás seguro de que deseas eliminar este grupo? Esta acción no se puede deshacer."
                                confirm-text="Eliminar"
                                confirm-class="btn-danger"
                            />
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modales -->
    @if(in_array($userRole, ['owner', 'admin']))
        <!-- Modal para invitar miembro -->
        <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-light" id="inviteModalLabel">
                            <i class="bi bi-person-plus text-primary"></i> Invitar Miembro
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('groups.invite', $group) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="invite_user_id" class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite_user_id" name="user_id" required>
                                    <option value="">Selecciona un usuario</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="invite_role" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite_role" name="role" required>
                                    <option value="member">Miembro</option>
                                    @if($isOwner)
                                        <option value="admin">Administrador</option>
                                    @endif
                                    <option value="viewer">Visualizador</option>
                                </select>
                                <small class="text-body-secondary d-block mt-1">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Miembro:</strong> Puede ver y usar items compartidos. 
                                    <strong>Administrador:</strong> Puede invitar miembros y gestionar el grupo. 
                                    <strong>Visualizador:</strong> Solo puede ver items compartidos.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Invitar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para cambiar rol -->
        <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-light" id="roleModalLabel">
                            <i class="bi bi-person-gear text-primary"></i> Cambiar Rol
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('groups.update-member-role', $group) }}" id="roleForm">
                        @csrf
                        <input type="hidden" name="member_id" id="role_member_id">
                        <div class="modal-body">
                            <p class="mb-3">
                                Cambiar rol de <strong id="role_member_name"></strong>:
                            </p>
                            <div class="mb-3">
                                <label for="role_select" class="form-label fw-semibold">Nuevo Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_select" name="role" required>
                                    <option value="admin">Administrador</option>
                                    <option value="member">Miembro</option>
                                    <option value="viewer">Visualizador</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar Rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        function openRoleModal(memberId, currentRole, memberName) {
            document.getElementById('role_member_id').value = memberId;
            document.getElementById('role_member_name').textContent = memberName;
            document.getElementById('role_select').value = currentRole;
            
            const modal = new bootstrap.Modal(document.getElementById('roleModal'));
            modal.show();
        }
    </script>
    @endpush
</x-app-layout>
