<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver a lista">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-person-plus text-primary"></i>
                <span>Crear Usuario</span>
            </h2>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="alert alert-info border-0 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    <span>El usuario podrá iniciar sesión con este email y contraseña. Comunica las credenciales por el canal que corresponda.</span>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-person-gear"></i> Datos del nuevo usuario
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

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
                                       value="{{ old('name') }}"
                                       required
                                       autofocus>
                            </div>
                            @error('name')
                                <x-input-error :messages="$errors->get('name')" />
                            @enderror
                        </div>

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
                                       value="{{ old('email') }}"
                                       required>
                            </div>
                            @error('email')
                                <x-input-error :messages="$errors->get('email')" />
                            @enderror
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Contraseña')" />
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required
                                       autocomplete="new-password">
                            </div>
                            @error('password')
                                <x-input-error :messages="$errors->get('password')" />
                            @enderror
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required
                                       autocomplete="new-password">
                            </div>
                            @error('password_confirmation')
                                <x-input-error :messages="$errors->get('password_confirmation')" />
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Crear usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
