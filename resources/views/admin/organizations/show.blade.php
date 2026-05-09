<x-admin-layout>
    <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-link btn-sm text-body-secondary text-decoration-none p-0">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary"></i>
                    <span>{{ $organization->name }}</span>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <form id="form-regenerate-password-org-{{ $organization->id }}" method="POST" action="{{ route('admin.organizations.regenerate-password', $organization) }}" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modal-regenerate-password-org-{{ $organization->id }}">
                        <i class="bi bi-key"></i> Regenerar contraseña temporal
                    </button>
                </form>
                <x-confirm-modal
                    id="modal-regenerate-password-org-{{ $organization->id }}"
                    form-id="form-regenerate-password-org-{{ $organization->id }}"
                    title="Regenerar contraseña temporal"
                    message="¿Regenerar la contraseña temporal del super admin de esta organización? El super admin deberá usar la nueva contraseña en el próximo login y cambiarla de inmediato."
                    confirm-text="Regenerar"
                    confirm-class="btn-warning"
                    cancel-text="Cancelar"
                />
                <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-body-secondary small mb-3">Información</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $organization->id }}</dd>
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $organization->name }}</dd>
                        <dt class="col-sm-4">Slug</dt>
                        <dd class="col-sm-8">
                            @if($organization->slug)
                                <code>&#64;{{ $organization->slug }}</code>
                                <span class="text-body-secondary small">(email: admin&#64;{{ $organization->slug }}.vault.local)</span>
                            @else
                                <span class="text-body-secondary">—</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Usuarios</dt>
                        <dd class="col-sm-8">{{ $organization->users_count }}</dd>
                        <dt class="col-sm-4">Creada</dt>
                        <dd class="col-sm-8">{{ $organization->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-people"></i> Usuarios ({{ $organization->users->count() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($organization->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organization->users as $u)
                                <tr>
                                    <td class="ps-4">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        @if($u->role === 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($u->role === 'admin')
                                            <span class="badge bg-warning text-dark">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Usuario</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-body-secondary py-4">
                    <i class="bi bi-people"></i>
                    <p class="mb-0 mt-2">Sin usuarios</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
