@if($items->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0 ps-4" style="width: 40px;"></th>
                            <th class="border-0">Título</th>
                            <th class="border-0">Tipo</th>
                            <th class="border-0">Propietario</th>
                            <th class="border-0">Creado</th>
                            <th class="border-0 text-end pe-4" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $hasTotp = $item->has_totp ?? false;
                                $userPermission = $item->user_permission ?? ($item->owner_user_id === Auth::id() ? 'owner' : 'view');
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    @if($item->favorite)
                                        <i class="bi bi-star-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-star text-body-secondary"></i>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vault.show', $item) }}" class="text-decoration-none text-body">
                                        <strong>{{ $item->title }}</strong>
                                        @if($hasTotp)
                                            <i class="bi bi-shield-check text-primary ms-2" title="Tiene autenticación de dos factores (TOTP)"></i>
                                        @endif
                                        @if($item->folder)
                                            <small class="text-body-secondary d-block mt-1">
                                                <i class="bi bi-folder"></i> {{ $item->folder->getFullPath() }}
                                            </small>
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        @if($item->type === 'auth')
                                            <i class="bi bi-key"></i> Auth
                                        @elseif($item->type === 'note')
                                            <i class="bi bi-sticky"></i> Nota
                                        @elseif($item->type === 'card')
                                            <i class="bi bi-credit-card"></i> Tarjeta
                                        @elseif($item->type === 'api_key')
                                            <i class="bi bi-code"></i> API Key
                                        @elseif($item->type === 'ssh_key')
                                            <i class="bi bi-terminal"></i> SSH Key
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="text-body-secondary">{{ $item->owner->name }}</span>
                                </td>
                                <td class="text-body-secondary small">
                                    {{ $item->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('vault.show', $item) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(in_array($userPermission, ['owner', 'admin', 'edit']))
                                            <a href="{{ route('vault.edit', $item) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($userPermission === 'owner')
                                            <form id="form-destroy-item-group-{{ $item->id }}" method="POST" action="{{ route('vault.destroy', $item) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#modal-destroy-item-group-{{ $item->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <x-confirm-modal
                                                id="modal-destroy-item-group-{{ $item->id }}"
                                                form-id="form-destroy-item-group-{{ $item->id }}"
                                                title="Eliminar item"
                                                message="¿Estás seguro de que deseas eliminar este item? Esta acción no se puede deshacer."
                                                confirm-text="Eliminar"
                                                confirm-class="btn-danger"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <i class="bi bi-inbox text-body-secondary" style="font-size: 4rem;"></i>
            <h4 class="mt-4 fw-light">No hay items compartidos con este grupo</h4>
            <p class="text-body-secondary">Comienza creando el primer item del grupo.</p>
            @if($userRole !== 'viewer')
                <a href="{{ route('groups.items.create', $group) }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Crear Primer Item
                </a>
            @endif
        </div>
    </div>
@endif
