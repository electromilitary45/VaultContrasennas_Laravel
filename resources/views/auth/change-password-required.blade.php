<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock-fill text-warning"></i>
            <span>Cambiar contraseña</span>
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                    <div>
                        <strong>Debes cambiar tu contraseña.</strong> Usaste una contraseña temporal. Elige una contraseña nueva para continuar.
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('password.change-required.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" required autocomplete="new-password"
                                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" />
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                                    class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" />
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle"></i>
                                <span>Guardar y continuar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
