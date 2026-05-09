<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-folder text-info"></i>
                <span>Mis Carpetas</span>
            </h2>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle text-info" style="font-size: 4rem;"></i>
                        <h4 class="mt-4 fw-light">Funcionalidad en desarrollo</h4>
                        <p class="text-body-secondary">Esta funcionalidad estará disponible próximamente.</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-info mt-3">
                            <i class="bi bi-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
