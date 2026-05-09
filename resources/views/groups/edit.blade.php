<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-pencil text-primary"></i>
                <span>Editar Grupo</span>
            </h2>
            <a href="{{ route('groups.admin', $group) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> 
                    <strong>Por favor, corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('groups.update', $group) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">Nombre del Grupo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $group->name) }}" 
                                   placeholder="Ej: Equipo de Desarrollo, Familia, etc." required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Describe el propósito del grupo...">{{ old('description', $group->description) }}</textarea>
                            <small class="text-body-secondary d-block mt-1">
                                <i class="bi bi-info-circle"></i> 
                                Opcional. Ayuda a identificar el propósito del grupo.
                            </small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Información -->
                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> Los cambios en el nombre y descripción no afectan a los miembros del grupo.
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('groups.admin', $group) }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
