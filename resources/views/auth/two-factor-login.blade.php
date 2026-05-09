<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-4">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
            <i class="bi bi-shield-check"></i>
        </div>
        <h2 class="h3 fw-light mb-2">Verificación de Dos Factores</h2>
        <p class="text-body-secondary small mb-0">
            Ingresa el código de 6 dígitos de tu aplicación de autenticación
        </p>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-circle"></i>
            <div>
                <strong>{{ $user->name }}</strong>
                <small class="text-body-secondary d-block">{{ $user->email }}</small>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="code" :value="__('Código de Verificación')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-shield-check"></i>
                </span>
                <input type="text" 
                       class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                       id="code" 
                       name="code" 
                       maxlength="6" 
                       pattern="[0-9]{6}"
                       placeholder="000000"
                       required
                       autofocus
                       autocomplete="one-time-code"
                       style="font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: 300;">
            </div>
            @error('code')
                <x-input-error :messages="$errors->get('code')" />
            @enderror
            <small class="text-body-secondary d-block mt-2">
                <i class="bi bi-info-circle"></i> También puedes usar un código de respaldo si lo tienes guardado.
            </small>
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-shield-check"></i> Verificar y Continuar
            </button>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Login
            </a>
        </div>
    </form>

    <div class="text-center">
        <small class="text-body-secondary">
            ¿Problemas con tu código? <a href="{{ route('login') }}" class="text-decoration-none">Inicia sesión nuevamente</a>
        </small>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('code');
            if (!codeInput) return;

            // Solo dígitos; paste limitado a 6 caracteres
            codeInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            codeInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                this.value = paste.replace(/[^0-9]/g, '').substring(0, 6);
            });
        });
    </script>
</x-guest-layout>
