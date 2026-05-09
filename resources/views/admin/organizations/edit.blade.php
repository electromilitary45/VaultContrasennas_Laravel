<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-link btn-sm text-body-secondary text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-pencil text-primary"></i>
                <span>Editar {{ $organization->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.organizations.update', $organization) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="name" class="form-label">Nombre de la organización <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $organization->name) }}"
                           required
                           maxlength="255">
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
                           value="{{ old('logo', $organization->logo) }}"
                           maxlength="500"
                           placeholder="URL o ruta del logo">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar
                    </button>
                    <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
