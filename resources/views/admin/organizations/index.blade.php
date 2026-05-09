<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-building text-primary"></i>
                <span>Organizaciones</span>
            </h2>
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Crear organización
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-list-ul"></i> Organizaciones ({{ $organizations->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($organizations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Nombre</th>
                                <th>Slug</th>
                                <th>Usuarios</th>
                                <th>Fecha</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizations as $org)
                                <tr>
                                    <td class="ps-4">
                                        <strong>{{ $org->name }}</strong>
                                    </td>
                                    <td>
                                        @if($org->slug)
                                            <code class="small">&#64;{{ $org->slug }}</code>
                                        @else
                                            <span class="text-body-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-people"></i> {{ $org->users_count }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $org->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('admin.organizations.show', $org) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.organizations.edit', $org) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0">
                    {{ $organizations->links() }}
                </div>
            @else
                <div class="text-center text-body-secondary py-5">
                    <i class="bi bi-building" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No hay organizaciones</p>
                    <a href="{{ route('admin.organizations.create') }}" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="bi bi-plus-lg"></i> Crear la primera
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
