<x-guest-layout>
    <h2 class="h3 fw-light mb-4">Restablecer Contraseña</h2>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input 
                id="email" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
                autocomplete="username"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <x-input-label for="password" :value="__('Nueva Contraseña')" />
            <x-text-input 
                id="password"
                type="password"
                name="password" 
                required 
                autocomplete="new-password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
            <x-text-input 
                id="password_confirmation"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>
                {{ __('Restablecer Contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
