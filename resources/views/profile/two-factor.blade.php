<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i>
                <span>Autenticación de Dos Factores (2FA)</span>
            </h2>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Perfil
            </a>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12 col-lg-8 mx-auto">
            @if($user->hasTwoFactorEnabled())
                <!-- 2FA Habilitado -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h4 class="h5 fw-light mb-1">2FA Habilitado</h2>
                                <p class="text-body-secondary small mb-0">
                                    Habilitado el {{ $user->totp_verified_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="bi bi-info-circle"></i>
                            <strong>2FA está activo</strong> en tu cuenta. Se requerirá un código de verificación cada vez que inicies sesión.
                        </div>

                        <!-- Códigos de Respaldo -->
                        @if($user->totp_backup_codes && count($user->totp_backup_codes) > 0)
                            <div class="mb-4">
                                <h5 class="h6 fw-light mb-3">Códigos de Respaldo</h5>
                                <p class="text-body-secondary small mb-3">
                                    Guarda estos códigos en un lugar seguro. Puedes usarlos para acceder a tu cuenta si pierdes acceso a tu dispositivo de autenticación.
                                </p>
                                <div class="bg-body-secondary p-3 rounded">
                                    <div class="row g-2">
                                        @foreach($user->totp_backup_codes as $code)
                                            <div class="col-6 col-md-4">
                                                <code class="d-block text-center p-2 bg-body border rounded">{{ $code }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('profile.two-factor.regenerate-backup-codes') }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i> Regenerar Códigos
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if(session('backup_codes'))
                            <div class="alert alert-warning border-0 shadow-sm">
                                <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Nuevos Códigos Generados</h6>
                                <p class="mb-2">Guarda estos códigos. Los códigos anteriores ya no funcionan.</p>
                                <div class="bg-body p-3 rounded">
                                    <div class="row g-2">
                                        @foreach(session('backup_codes') as $code)
                                            <div class="col-6 col-md-4">
                                                <code class="d-block text-center p-2 bg-white border rounded">{{ $code }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Deshabilitar 2FA -->
                        <div class="border-top pt-4">
                            <h5 class="h6 fw-light mb-3 text-danger">Zona de Peligro</h5>
                            <form id="form-disable-2fa" method="POST" action="{{ route('profile.two-factor.disable') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-disable-2fa">
                                    <i class="bi bi-shield-x"></i> Deshabilitar 2FA
                                </button>
                            </form>
                            <x-confirm-modal
                                id="modal-disable-2fa"
                                form-id="form-disable-2fa"
                                title="Deshabilitar 2FA"
                                message="¿Estás seguro de que deseas deshabilitar 2FA? Esto reducirá la seguridad de tu cuenta."
                                confirm-text="Deshabilitar 2FA"
                                confirm-class="btn-danger"
                            />
                        </div>
                    </div>
                </div>
            @else
                <!-- 2FA No Habilitado - Configuración -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h4 class="h5 fw-light mb-2">Habilitar Autenticación de Dos Factores</h4>
                            <p class="text-body-secondary">
                                Añade una capa adicional de seguridad a tu cuenta usando códigos de verificación.
                            </p>
                        </div>

                        @if($secret && $qrCodeUri)
                            <!-- Paso 1: Escanear QR -->
                            <div class="mb-4">
                                <h5 class="h6 fw-light mb-3">Paso 1: Escanea el código QR</h5>
                                <p class="text-body-secondary small mb-3">
                                    Usa una aplicación de autenticación como Google Authenticator, Microsoft Authenticator o Authy para escanear este código.
                                </p>
                                <div class="text-center mb-3">
                                    <div class="d-inline-block p-3 bg-white border rounded shadow-sm">
                                        <!-- QR Code usando API externa -->
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUri) }}" 
                                             alt="QR Code para 2FA" 
                                             class="img-fluid">
                                    </div>
                                </div>
                                <div class="bg-body-secondary p-3 rounded mb-3">
                                    <small class="text-body-secondary d-block mb-2"><strong>O ingresa este código manualmente:</strong></small>
                                    <code class="d-block text-center">{{ $secret }}</code>
                                </div>
                            </div>

                            <!-- Paso 2: Verificar Código -->
                            <div class="border-top pt-4">
                                <h5 class="h6 fw-light mb-3">Paso 2: Verifica el código</h5>
                                <p class="text-body-secondary small mb-3">
                                    Ingresa el código de 6 dígitos que aparece en tu aplicación de autenticación.
                                </p>
                                <form method="POST" action="{{ route('profile.two-factor.enable') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Código de Verificación</label>
                                        <input type="text" 
                                               class="form-control @error('code') is-invalid @enderror" 
                                               id="code" 
                                               name="code" 
                                               maxlength="6" 
                                               pattern="[0-9]{6}"
                                               placeholder="000000"
                                               required
                                               autofocus>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Habilitar 2FA
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
