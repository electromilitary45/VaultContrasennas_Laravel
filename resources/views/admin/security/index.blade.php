<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-shield-exclamation text-warning"></i>
            <span>Reportes de Seguridad</span>
        </h2>
    </x-slot>

    <!-- Tarjetas de reportes disponibles -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.security.weak-passwords') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-shield-x text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-semibold">Contraseñas Débiles</h5>
                                <p class="text-body-secondary small mb-3">Items con contraseñas que no cumplen estándares de seguridad</p>
                                <span class="text-danger small fw-semibold">
                                    Ver reporte <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.security.reused-passwords') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-arrow-repeat text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-semibold">Contraseñas Reutilizadas</h5>
                                <p class="text-body-secondary small mb-3">Items que comparten la misma contraseña</p>
                                <span class="text-warning small fw-semibold">
                                    Ver reporte <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.security.inactive-users') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-person-x text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-semibold">Usuarios Inactivos</h5>
                                <p class="text-body-secondary small mb-3">Usuarios que no han iniciado sesión recientemente</p>
                                <span class="text-info small fw-semibold">
                                    Ver reporte <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.security.failed-logins') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-semibold">Intentos de Login Fallidos</h5>
                                <p class="text-body-secondary small mb-3">Intentos de autenticación fallidos recientes</p>
                                <span class="text-danger small fw-semibold">
                                    Ver reporte <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('admin.security.report') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-clipboard-data text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-semibold">Reporte General</h5>
                                <p class="text-body-secondary small mb-3">Vista general de la seguridad del sistema</p>
                                <span class="text-primary small fw-semibold">
                                    Ver reporte <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0 pb-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-info-circle text-body-secondary"></i> Información
            </h6>
        </div>
        <div class="card-body pt-3">
            <p class="text-body-secondary mb-0 small">
                Los reportes de seguridad analizan los datos del sistema para identificar posibles vulnerabilidades.
                Estos reportes solo muestran metadatos y no exponen los secretos cifrados directamente.
                Para verificar contraseñas comprometidas, se implementará la integración con Have I Been Pwned (Pwned Passwords API - gratuita).
            </p>
        </div>
    </div>
</x-admin-layout>
