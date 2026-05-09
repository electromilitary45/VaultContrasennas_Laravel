<x-guest-layout>
    <h2 class="h3 fw-light mb-4">Recuperar Contraseña</h2>

        <p class="text-body-secondary small mb-4">
        {{ __('¿Olvidaste tu contraseña? No hay problema. Solo indícanos tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña.') }}
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input 
                id="email" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>
                {{ __('Enviar Enlace de Restablecimiento') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 pt-4 border-top">
        <p class="text-body-secondary small mb-0 text-center">
            <a href="{{ route('login') }}" class="text-decoration-none">Volver al inicio de sesión</a>
        </p>
    </div>
</x-guest-layout>
