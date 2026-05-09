<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-key-fill text-success"></i>
                <span>Contraseña temporal regenerada</span>
            </h2>
        </div>
    </x-slot>

    <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle fs-4"></i>
            <div>
                <strong>Guarda estas credenciales.</strong> No se volverán a mostrar. El super admin de la organización debe usarlas en el próximo login; se le pedirá cambiar la contraseña de inmediato.
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-building"></i> {{ $organization->name }}
            </h6>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-body-secondary">Email</dt>
                <dd class="col-sm-9"><code>{{ $email }}</code></dd>
                <dt class="col-sm-3 text-body-secondary">Nueva contraseña temporal</dt>
                <dd class="col-sm-9"><code>{{ $password }}</code></dd>
            </dl>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-primary">
            <i class="bi bi-building"></i> Ver organización
        </a>
        <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list-ul"></i> Ir a organizaciones
        </a>
    </div>
</x-admin-layout>
