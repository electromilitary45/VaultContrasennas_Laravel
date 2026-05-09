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
                            <th class="border-0">Creado</th>
                            <th class="border-0 text-end pe-4" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                // Usar el flag has_totp agregado por el controlador
                                $hasTotp = $item->has_totp ?? false;
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
                                        @if(isset($item->is_shared) && $item->is_shared)
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @if($item->shared_by_user)
                                                    <span class="badge bg-info" title="Compartido directamente por {{ $item->shared_by_user->name ?? 'otro usuario' }}">
                                                        <i class="bi bi-person"></i> {{ $item->shared_by_user->name ?? 'Usuario' }}
                                                    </span>
                                                @endif
                                                @if(!empty($item->shared_by_groups))
                                                    @foreach($item->shared_by_groups as $groupShare)
                                                        <span class="badge bg-success" title="Compartido por el grupo {{ $groupShare['group']->name ?? 'Grupo' }}">
                                                            <i class="bi bi-people"></i> {{ $groupShare['group']->name ?? 'Grupo' }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
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
                                        @elseif($item->type === 'env_file')
                                            <i class="bi bi-file-earmark-code"></i> .env
                                        @endif
                                    </span>
                                </td>
                                <td class="text-body-secondary small">
                                    {{ $item->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    @php
                                        $userPermission = $item->user_permission ?? ($item->owner_user_id === Auth::id() ? 'owner' : 'view');
                                    @endphp
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
                                            <form id="form-destroy-item-{{ $item->id }}" method="POST" action="{{ route('vault.destroy', $item) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#modal-destroy-item-{{ $item->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <x-confirm-modal
                                                id="modal-destroy-item-{{ $item->id }}"
                                                form-id="form-destroy-item-{{ $item->id }}"
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
            <h4 class="mt-4 fw-light">No hay items en tu vault</h4>
            <p class="text-body-secondary">Comienza creando tu primer secreto.</p>
            <a href="{{ route('vault.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Crear Primer Item
            </a>
        </div>
    </div>
@endif
