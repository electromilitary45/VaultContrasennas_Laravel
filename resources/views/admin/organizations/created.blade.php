<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle text-success"></i>
                <span>Organización creada</span>
            </h2>
        </div>
    </x-slot>

    <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle fs-4"></i>
            <div>
                <strong>Guarda estas credenciales.</strong> No se volverán a mostrar. Usa la contraseña temporal solo para el primer acceso; se te pedirá cambiarla de inmediato. Si las pierdes, el platform super admin puede regenerar una contraseña temporal desde el detalle de la organización.
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
                <dt class="col-sm-3 text-body-secondary">Contraseña temporal</dt>
                <dd class="col-sm-9"><code>{{ $password }}</code></dd>
            </dl>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.organizations.index') }}" class="btn btn-primary">
            <i class="bi bi-list-ul"></i> Ir a organizaciones
        </a>
        <a href="{{ route('admin.organizations.create') }}" class="btn btn-outline-primary">
            <i class="bi bi-plus-lg"></i> Crear otra
        </a>
    </div>
</x-admin-layout>
