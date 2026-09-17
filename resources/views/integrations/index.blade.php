<x-app-layout>
    <x-slot name="header">
        <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-puzzle text-primary"></i>
            <span>Integraciones</span>
        </h2>
    </x-slot>

    <div class="container-fluid px-4">
        <p class="text-body-secondary mb-4">
            Conecta PassVault con otras herramientas. La extensión de navegador te permite acceder al vault desde cualquier página.
        </p>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-browser-chrome text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="h5 fw-semibold mb-2">Extensión para Chrome</h3>
                                <p class="text-body-secondary small mb-3">
                                    Abre el popup desde la barra de extensiones para ver tus items, copiar usuario/contraseña y rellenar formularios de login automáticamente. Usa la misma sesión que la web (inicia sesión en el vault en una pestaña).
                                </p>
                                <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                                    <span class="badge bg-secondary">Versión actual: {{ $extensionVersion }}</span>
                                    <a href="{{ route('integrations.extension.download') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-download"></i> Descargar extensión
                                    </a>
                                    <a href="{{ route('vault.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-shield-lock"></i> Ir al Vault
                                    </a>
                                </div>
                                <p class="small text-body-secondary mb-2">
                                    La extensión descargada queda configurada para este servidor: <strong>{{ config('app.url') }}</strong> (local o producción según desde dónde descargues).
                                </p>
                                <div class="border rounded p-3 bg-body-secondary bg-opacity-50">
                                    <h6 class="small fw-semibold mb-2">Instalación</h6>
                                    <ol class="small mb-0 ps-3 text-body-secondary">
                                        <li>Descarga la extensión con el botón de arriba y descomprime el ZIP en una carpeta.</li>
                                        <li>Abre Chrome y ve a <code>chrome://extensions</code>.</li>
                                        <li>Activa "Modo desarrollador" y pulsa "Cargar descomprimida". Selecciona la carpeta que descomprimiste.</li>
                                        <li>Abre el vault en una pestaña (ej. <a href="{{ url('/') }}">{{ url('/') }}</a>) e inicia sesión.</li>
                                        <li>Haz clic en el icono de la extensión para abrir el popup.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
