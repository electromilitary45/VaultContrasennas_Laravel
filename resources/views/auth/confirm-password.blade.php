<x-guest-layout>
    <h2 class="h3 fw-light mb-4">Confirmar Contraseña</h2>

        <p class="text-body-secondary small mb-4">
        {{ __('Esta es un área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input 
                id="password"
                type="password"
                name="password"
                required 
                autocomplete="current-password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>
                {{ __('Confirmar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
