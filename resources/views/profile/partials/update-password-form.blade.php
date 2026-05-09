<section>
    <header class="mb-4">
        <h3 class="h5 fw-light mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-primary"></i>
            <span>{{ __('Actualizar Contraseña') }}</span>
        </h3>
        <p class="text-body-secondary small mb-0">
            {{ __('Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerla segura.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" :value="__('Contraseña Actual')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-lock"></i>
                </span>
                <x-text-input 
                    id="update_password_current_password" 
                    name="current_password" 
                    type="password" 
                    autocomplete="current-password"
                    class="form-control border-light {{ $errors->updatePassword->get('current_password') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password" :value="__('Nueva Contraseña')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <x-text-input 
                    id="update_password_password" 
                    name="password" 
                    type="password" 
                    autocomplete="new-password"
                    class="form-control border-light {{ $errors->updatePassword->get('password') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-4">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <x-text-input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    autocomplete="new-password"
                    class="form-control border-light {{ $errors->updatePassword->get('password_confirmation') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle"></i>
                <span>{{ __('Guardar') }}</span>
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <div class="d-flex align-items-center gap-2 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span class="small">{{ __('Guardado.') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
