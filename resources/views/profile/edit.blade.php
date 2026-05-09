<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i>
                <span>Perfil</span>
            </h2>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Avatar y información básica -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-5 text-center">
                    <!-- Avatar grande -->
                    <div class="mb-4 position-relative d-inline-block">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->getAvatarUrl() }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 3rem; font-weight: 300;">
                                {{ Auth::user()->getInitials() }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <label for="avatar" class="btn btn-sm btn-outline-primary mb-2">
                                <i class="bi bi-camera"></i> Cambiar Avatar
                            </label>
                            <input type="file" 
                                   id="avatar" 
                                   name="avatar" 
                                   accept="image/*" 
                                   class="d-none" 
                                   onchange="this.form.submit()">
                        </form>
                        @if(Auth::user()->avatar)
                            <form id="form-delete-avatar" method="POST" action="{{ route('profile.avatar.delete') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-avatar">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </form>
                            <x-confirm-modal
                                id="modal-delete-avatar"
                                form-id="form-delete-avatar"
                                title="Eliminar avatar"
                                message="¿Eliminar avatar?"
                                confirm-text="Eliminar"
                                confirm-class="btn-danger"
                            />
                        @endif
                    </div>
                    <h4 class="h5 fw-light mb-2">{{ Auth::user()->name }}</h4>
                    <p class="text-body-secondary small mb-0">{{ Auth::user()->email }}</p>
                    <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                        @if (Auth::user()->email_verified_at)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle-fill"></i> Email Verificado
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle-fill"></i> Email No Verificado
                            </span>
                        @endif
                        @if (Auth::user()->hasTwoFactorEnabled())
                            <span class="badge bg-success">
                                <i class="bi bi-shield-check"></i> 2FA Activo
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-shield-x"></i> 2FA Inactivo
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('profile.two-factor') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-shield-check"></i> Configurar 2FA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formularios -->
        <div class="col-12 col-lg-8">
            @if(session('status'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                    @if(session('status') === 'profile-updated')
                        <i class="bi bi-check-circle-fill"></i> Perfil actualizado correctamente.
                    @elseif(session('status') === 'avatar-updated')
                        <i class="bi bi-check-circle-fill"></i> Avatar actualizado correctamente.
                    @elseif(session('status') === 'avatar-deleted')
                        <i class="bi bi-check-circle-fill"></i> Avatar eliminado correctamente.
                    @elseif(session('status') === 'two-factor-enabled')
                        <i class="bi bi-check-circle-fill"></i> 2FA habilitado correctamente.
                    @elseif(session('status') === 'two-factor-disabled')
                        <i class="bi bi-check-circle-fill"></i> 2FA deshabilitado correctamente.
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                <!-- Información del Perfil -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <!-- Actualizar Contraseña -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <!-- Eliminar Cuenta -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm border-danger">
                        <div class="card-body p-5">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
