<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h3 fw-light mb-2 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-person-plus-fill text-primary"></i>
            <span>Crear Cuenta</span>
        </h2>
        <p class="text-body-secondary small mb-0">Necesitas un código de invitación de tu organización</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Código de invitación -->
        <div class="mb-3">
            <x-input-label for="invitation_code" :value="__('Código de invitación')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-ticket-perforated"></i>
                </span>
                <x-text-input
                    id="invitation_code"
                    type="text"
                    name="invitation_code"
                    :value="old('invitation_code', $prefillCode ?? '')"
                    required
                    autofocus
                    autocomplete="off"
                    placeholder="Pega el código que te compartieron"
                    class="form-control font-monospace {{ $errors->has('invitation_code') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('invitation_code')" />
        </div>

        <!-- Name -->
        <div class="mb-3">
            <x-input-label for="name" :value="__('Nombre')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-person"></i>
                </span>
                <x-text-input
                    id="name"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autocomplete="name"
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-envelope"></i>
                </span>
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-lock"></i>
                </span>
                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <x-text-input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button class="d-flex align-items-center gap-2">
                <i class="bi bi-person-plus"></i>
                <span>{{ __('Registrarse') }}</span>
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 pt-4 border-top">
        <p class="text-body-secondary small mb-0 text-center">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Inicia sesión</a>
        </p>
    </div>
</x-guest-layout>
