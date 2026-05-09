<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-link btn-sm text-body-secondary text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-building-add text-primary"></i>
                <span>Crear organización</span>
            </h2>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <p class="text-body-secondary small mb-4">
                Se creará la organización y un usuario super admin para gestionarla. Las credenciales se mostrarán una sola vez.
            </p>
            <form method="POST" action="{{ route('admin.organizations.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="name" class="form-label">Nombre de la organización <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required
                           maxlength="255"
                           placeholder="Ej. Acme Corp">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="logo" class="form-label">Logo <span class="text-body-secondary">(opcional)</span></label>
                    <input type="text"
                           name="logo"
                           id="logo"
                           class="form-control @error('logo') is-invalid @enderror"
                           value="{{ old('logo') }}"
                           maxlength="500"
                           placeholder="URL o ruta del logo">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Crear organización
                    </button>
                    <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
