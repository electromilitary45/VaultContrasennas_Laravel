<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-4">
        <h2 class="h3 fw-light mb-2 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-in-right text-primary"></i>
            <span>Iniciar Sesión</span>
        </h2>
        <p class="text-body-secondary small mb-0">Accede a tu bóveda de secretos</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                    autofocus 
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
                    autocomplete="current-password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="remember_me" 
                    name="remember"
                >
                <label class="form-check-label small" for="remember_me">
                    {{ __('Recordarme') }}
                </label>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a class="text-body-emphasis small text-decoration-none d-flex align-items-center gap-1" href="{{ route('password.request') }}">
                    <i class="bi bi-question-circle"></i>
                    <span>{{ __('¿Olvidaste tu contraseña?') }}</span>
                </a>
            @endif

            <x-primary-button class="d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>{{ __('Iniciar Sesión') }}</span>
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 pt-4 border-top">
        <p class="text-body-secondary small mb-0 text-center">
            ¿No tienes cuenta? 
            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Regístrate</a>
        </p>
    </div>
</x-guest-layout>
