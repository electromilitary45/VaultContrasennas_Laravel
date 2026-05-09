<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary btn-sm" title="Volver a detalle">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-pencil text-primary"></i>
                <span>Editar Usuario</span>
            </h2>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Mensajes de éxito/error -->
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

            <!-- Formulario de Edición -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-person-gear"></i> Información del Usuario
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nombre')" />
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required 
                                       autofocus>
                            </div>
                            @error('name')
                                <x-input-error :messages="$errors->get('name')" />
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                            </div>
                            @error('email')
                                <x-input-error :messages="$errors->get('email')" />
                            @enderror
                        </div>

                        <!-- Verificación de Email -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="email_verified" 
                                       name="email_verified" 
                                       value="1"
                                       {{ $user->email_verified_at ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verified">
                                    <strong>Email verificado</strong>
                                    <small class="text-body-secondary d-block">
                                        Marca esta opción para verificar manualmente el email del usuario
                                    </small>
                                </label>
                            </div>
                        </div>

                        <!-- Información de solo lectura -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-info-circle"></i>
                                <strong>Información adicional</strong>
                            </div>
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8"><code>{{ $user->id }}</code></dd>
                                
                                <dt class="col-sm-4">Rol:</dt>
                                <dd class="col-sm-8">
                                    @if($user->isSuperAdmin())
                                        <span class="badge bg-danger">Super Administrador</span>
                                    @elseif($user->isAdmin())
                                        <span class="badge bg-warning">Administrador</span>
                                    @else
                                        <span class="badge bg-secondary">Usuario</span>
                                    @endif
                                    <small class="text-body-secondary d-block mt-1">
                                        Para cambiar el rol, usa la función específica de cambio de rol
                                    </small>
                                </dd>
                                
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    @if($user->is_active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                    <small class="text-body-secondary d-block mt-1">
                                        Para activar/desactivar, usa las acciones en la vista de detalle
                                    </small>
                                </dd>
                                
                                <dt class="col-sm-4">Registro:</dt>
                                <dd class="col-sm-8">
                                    {{ $user->created_at->format('d/m/Y H:i:s') }}
                                </dd>
                                
                                @if($user->hasTwoFactorEnabled())
                                    <dt class="col-sm-4">2FA:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-info">Activado</span>
                                        <small class="text-body-secondary d-block mt-1">
                                            El usuario tiene 2FA configurado
                                        </small>
                                    </dd>
                                @endif
                            </dl>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
