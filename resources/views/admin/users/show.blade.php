<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver a lista">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-person text-primary"></i>
                <span>Detalle de Usuario</span>
            </h2>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i>
                    <span class="d-none d-sm-inline">Editar</span>
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                        <span class="d-none d-sm-inline">Acciones</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(Auth::user()->isSuperAdmin())
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#changeRoleModal">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Cambiar Rol</span>
                                </button>
                            </li>
                        @endif
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                <i class="bi bi-key"></i>
                                <span>Resetear Contraseña</span>
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @if($user->is_active)
                            <li>
                                <form id="form-deactivate-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="d-inline">
                                    @csrf
                                    @method('POST')
                                    <button type="button" class="dropdown-item text-warning d-flex align-items-center gap-2 w-100" data-bs-toggle="modal" data-bs-target="#modal-deactivate-user-{{ $user->id }}">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>Desactivar</span>
                                    </button>
                                </form>
                                <x-confirm-modal
                                    id="modal-deactivate-user-{{ $user->id }}"
                                    form-id="form-deactivate-user-{{ $user->id }}"
                                    title="Desactivar usuario"
                                    message="¿Desactivar este usuario? No podrá iniciar sesión hasta que sea reactivado."
                                    confirm-text="Desactivar"
                                    confirm-class="btn-warning"
                                />
                            </li>
                        @else
                            <li>
                                <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="d-inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="dropdown-item text-success d-flex align-items-center gap-2 w-100">
                                        <i class="bi bi-play-circle"></i>
                                        <span>Activar</span>
                                    </button>
                                </form>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form id="form-destroy-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="dropdown-item text-danger d-flex align-items-center gap-2 w-100" data-bs-toggle="modal" data-bs-target="#modal-destroy-user-{{ $user->id }}">
                                    <i class="bi bi-trash"></i>
                                    <span>Eliminar</span>
                                </button>
                            </form>
                            <x-confirm-modal
                                id="modal-destroy-user-{{ $user->id }}"
                                form-id="form-destroy-user-{{ $user->id }}"
                                title="Eliminar usuario"
                                message="¿Eliminar este usuario? Esta acción no se puede deshacer."
                                confirm-text="Eliminar"
                                confirm-class="btn-danger"
                            />
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> 
            {{ session('error') ?? $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Información del Usuario -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    @if($user->avatar)
                        <img src="{{ $user->getAvatarUrl() }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle mb-3" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ $user->getInitials() }}
                        </div>
                    @endif
                    <h4 class="fw-light mb-2">{{ $user->name }}</h4>
                    <p class="text-body-secondary mb-3">{{ $user->email }}</p>
                    
                    <div class="d-flex flex-column gap-2">
                        @if($user->isSuperAdmin())
                            <span class="badge bg-danger">Super Administrador</span>
                        @elseif($user->isAdmin())
                            <span class="badge bg-warning">Administrador</span>
                        @else
                            <span class="badge bg-secondary">Usuario</span>
                        @endif
                        
                        @if($user->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Activo
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactivo
                            </span>
                        @endif
                        
                        @if($user->email_verified_at)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Email Verificado
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle"></i> Email Sin Verificar
                            </span>
                        @endif
                        
                        @if($user->hasTwoFactorEnabled())
                            <span class="badge bg-info">
                                <i class="bi bi-shield-check"></i> 2FA Activado
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
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
                        <dd class="col-sm-8"><code>{{ $user->id }}</code></dd>
                        
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        
                        <dt class="col-sm-4">Rol:</dt>
                        <dd class="col-sm-8">
                            @if($user->isSuperAdmin())
                                <span class="badge bg-danger">Super Administrador</span>
                            @elseif($user->isAdmin())
                                <span class="badge bg-warning text-dark">Administrador</span>
                            @else
                                <span class="badge bg-secondary">Usuario</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($user->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Registro:</dt>
                        <dd class="col-sm-8">
                            {{ $user->created_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $user->created_at->diffForHumans() }}
                            </small>
                        </dd>
                        
                        <dt class="col-sm-4">Última actualización:</dt>
                        <dd class="col-sm-8">
                            {{ $user->updated_at->format('d/m/Y H:i:s') }}
                            <small class="text-body-secondary d-block">
                                {{ $user->updated_at->diffForHumans() }}
                            </small>
                        </dd>
                        
                        @if($user->email_verified_at)
                            <dt class="col-sm-4">Email verificado:</dt>
                            <dd class="col-sm-8">
                                {{ $user->email_verified_at->format('d/m/Y H:i:s') }}
                            </dd>
                        @endif
                        
                        @if($user->hasTwoFactorEnabled() && $user->totp_verified_at)
                            <dt class="col-sm-4">2FA verificado:</dt>
                            <dd class="col-sm-8">
                                {{ $user->totp_verified_at->format('d/m/Y H:i:s') }}
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-1 small">Vault Items</h6>
                    <h3 class="mb-0">{{ number_format($stats['vault_items']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-1 small">Grupos Propios</h6>
                    <h3 class="mb-0">{{ number_format($stats['groups_owned']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-1 small">Miembro de Grupos</h6>
                    <h3 class="mb-0">{{ number_format($stats['groups_member']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-body-secondary mb-1 small">Items Compartidos</h6>
                    <h3 class="mb-0">{{ number_format($stats['shared_items']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Recientes -->
    @if($recentItems->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-shield-lock"></i> Items Recientes
                </h6>
                <a href="{{ route('vault.index', ['user_id' => $user->id]) }}" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentItems as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($item->type) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $item->created_at->format('d/m/Y') }}
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

    <!-- Grupos -->
    @if($groups->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-body border-0">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-diagram-3"></i> Grupos
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($groups as $group)
                        <div class="col-md-6">
                            <div class="card border border-secondary">
                                <div class="card-body">
                                    <h6 class="mb-1">{{ $group->name }}</h6>
                                    @if($group->description)
                                        <p class="text-body-secondary small mb-2">{{ $group->description }}</p>
                                    @endif
                                    <div class="d-flex align-items-center gap-2">
                                        @if($group->owner_user_id === $user->id)
                                            <span class="badge bg-primary">Propietario</span>
                                        @else
                                            <span class="badge bg-info">{{ $group->getUserRole($user->id) ?? 'Miembro' }}</span>
                                        @endif
                                        @php
                                            $memberCount = $group->active_members_count ?? $group->members()->where('status', 'active')->count();
                                        @endphp
                                        @if($memberCount > 0)
                                            <small class="text-body-secondary ms-auto">
                                                <i class="bi bi-people"></i> {{ $memberCount }} {{ $memberCount === 1 ? 'miembro' : 'miembros' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Logs Recientes -->
    @if($recentLogs->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body border-0">
                <h6 class="mb-0 fw-light">
                    <i class="bi bi-clipboard-check"></i> Actividad Reciente
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Fecha</th>
                                <th>Acción</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                                <tr>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->action === 'create' ? 'success' : ($log->action === 'update' ? 'primary' : 'danger') }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">{{ class_basename($log->model_type) }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Cambiar Rol -->
    @if(Auth::user()->isSuperAdmin())
        <div class="modal fade" id="changeRoleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.users.change-role', $user) }}">
                        @csrf
                        @method('POST')
                        <div class="modal-header">
                            <h5 class="modal-title">Cambiar Rol de Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Selecciona el nuevo rol para <strong>{{ $user->name }}</strong>:</p>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rol</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Administrador</option>
                                </select>
                            </div>
                            <div class="alert alert-warning border-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                <small>Esta acción cambiará los permisos del usuario inmediatamente.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Cambiar Rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Resetear Contraseña -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    @method('POST')
                    <div class="modal-header">
                        <h5 class="modal-title">Resetear Contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div>
                                <strong>Advertencia Ética:</strong> Esta acción cambiará la contraseña del usuario sin su conocimiento previo.
                                <ul class="mb-0 mt-2 small">
                                    <li>Esta acción quedará registrada en los logs de auditoría</li>
                                    <li>El usuario deberá usar la nueva contraseña para iniciar sesión</li>
                                    <li>La notificación por email se implementará cuando se configure el sistema de correo</li>
                                </ul>
                            </div>
                        </div>
                        <p class="mb-3">Ingresa una nueva contraseña para <strong>{{ $user->name }}</strong>:</p>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                            @error('password')
                                <x-input-error :messages="$errors->get('password')" />
                            @enderror
                            <small class="text-body-secondary">Mínimo 8 caracteres</small>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>
                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle"></i>
                            <small>
                                <strong>Importante:</strong> Esta acción será registrada en los logs de auditoría con información del administrador que realizó el cambio.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Resetear Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
