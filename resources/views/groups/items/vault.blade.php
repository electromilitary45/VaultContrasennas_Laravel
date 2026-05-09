<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-primary"></i>
                <span>Items del Grupo: {{ $group->name }}</span>
            </h2>
            <div class="d-flex gap-2">
                @if($userRole !== 'viewer')
                    <a href="{{ route('groups.items.create', $group) }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i>
                        <span>Nuevo Item</span>
                    </a>
                @endif
                @if(in_array($userRole, ['owner', 'admin']))
                    <a href="{{ route('groups.admin', $group) }}" class="btn btn-outline-primary">
                        <i class="bi bi-gear"></i> Administrar Grupo
                    </a>
                @endif
                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Grupos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <!-- Sidebar de Carpetas del Grupo -->
        <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-folder"></i> Carpetas del Grupo
                    </h6>
                    @if($userRole !== 'viewer')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal" title="Nueva carpeta">
                            <i class="bi bi-plus"></i>
                        </button>
                    @endif
                </div>
                <div class="card-body p-0" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        <!-- Sin carpeta -->
                        <a href="{{ route('groups.items.index', ['group' => $group->id, 'folder_id' => 'null'] + request()->except('folder_id')) }}" 
                           class="list-group-item list-group-item-action {{ ($activeFolderId ?? null) === 'null' || ($activeFolderId ?? null) === null ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-folder-x text-body-secondary"></i> Sin carpeta
                            </span>
                            <span class="badge bg-secondary rounded-pill">{{ $unassignedCount ?? 0 }}</span>
                        </a>
                        
                        <!-- Carpetas del grupo -->
                        @if($folders && $folders->count() > 0)
                            @foreach($folders as $folder)
                                @include('groups.items.partials.folder-item', [
                                    'folder' => $folder,
                                    'group' => $group,
                                    'activeFolderId' => $activeFolderId ?? null,
                                    'level' => 0,
                                    'userRole' => $userRole
                                ])
                            @endforeach
                        @else
                            <div class="list-group-item text-center text-body-secondary py-4">
                                <i class="bi bi-folder" style="font-size: 2rem;"></i>
                                <p class="small mt-2 mb-0">No hay carpetas en el grupo</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-lg-9 col-md-8">
            <!-- Breadcrumbs -->
            @if($activeFolderId && $activeFolderId !== 'null')
                @php
                    $activeFolder = $folders->flatten()->firstWhere('id', (int)$activeFolderId) 
                        ?? \App\Models\Folder::find($activeFolderId);
                @endphp
                @if($activeFolder)
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('groups.items.index', $group) }}" class="text-decoration-none">
                                    <i class="bi bi-house"></i> Items del Grupo
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $activeFolder->getFullPath() }}
                            </li>
                        </ol>
                    </nav>
                @endif
            @endif

            <!-- Filtros -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('groups.items.index', $group) }}" id="filterForm" class="row g-3 align-items-end">
                        <input type="hidden" name="folder_id" id="folder_id_input" value="{{ $activeFolderId ?? '' }}">
                        <div class="col-md-4">
                            <label for="search" class="form-label small text-body-secondary">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Buscar por título...">
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label small text-body-secondary">Tipo</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Todos</option>
                                <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Autenticación</option>
                                <option value="note" {{ request('type') === 'note' ? 'selected' : '' }}>Nota</option>
                                <option value="card" {{ request('type') === 'card' ? 'selected' : '' }}>Tarjeta</option>
                                <option value="api_key" {{ request('type') === 'api_key' ? 'selected' : '' }}>API Key</option>
                                <option value="ssh_key" {{ request('type') === 'ssh_key' ? 'selected' : '' }}>SSH Key</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="favorite" name="favorite" value="1"
                                       {{ request('favorite') ? 'checked' : '' }}>
                                <label class="form-check-label" for="favorite">
                                    Solo favoritos
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de items -->
            <div id="vaultItemsContainer">
                @include('groups.items.partials.items-list', ['items' => $items, 'group' => $group])
            </div>
        </div>
    </div>

    <!-- Modal para crear carpeta del grupo -->
    @if($userRole !== 'viewer')
        @include('groups.items.partials.folder-modal', [
            'modalId' => 'createFolderModal', 
            'title' => 'Nueva Carpeta del Grupo', 
            'action' => route('groups.folders.store', $group), 
            'method' => 'POST',
            'allFolders' => $allFolders ?? collect(),
            'group' => $group
        ])

        <!-- Modal para editar carpeta del grupo -->
        @include('groups.items.partials.folder-modal', [
            'modalId' => 'editFolderModal', 
            'title' => 'Editar Carpeta del Grupo', 
            'action' => '', 
            'method' => 'PUT',
            'allFolders' => $allFolders ?? collect(),
            'group' => $group
        ])
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('search');
            const typeSelect = document.getElementById('type');
            const favoriteCheckbox = document.getElementById('favorite');
            const itemsContainer = document.getElementById('vaultItemsContainer');
            
            if (!form || !itemsContainer) return;

            let debounceTimer;
            let isLoading = false;

            // Función para actualizar los resultados vía AJAX
            async function updateResults() {
                if (isLoading) return;
                
                isLoading = true;
                
                const formData = new FormData(form);
                const params = new URLSearchParams();
                
                if (formData.get('search')) {
                    params.append('search', formData.get('search'));
                }
                if (formData.get('type')) {
                    params.append('type', formData.get('type'));
                }
                if (formData.get('favorite')) {
                    params.append('favorite', formData.get('favorite'));
                }
                const folderIdInput = document.getElementById('folder_id_input');
                if (folderIdInput && folderIdInput.value) {
                    params.append('folder_id', folderIdInput.value);
                }
                
                const url = '{{ route('groups.items.index', $group) }}?' + params.toString();
                
                try {
                    itemsContainer.style.opacity = '0.6';
                    
                    const response = await window.axios.get(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });
                    
                    itemsContainer.innerHTML = response.data;
                    window.history.pushState({}, '', url);
                    
                } catch (error) {
                    console.error('Error al cargar resultados:', error);
                    window.location.href = url;
                } finally {
                    isLoading = false;
                    itemsContainer.style.opacity = '1';
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function() {
                        updateResults();
                    }, 500);
                });

                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        clearTimeout(debounceTimer);
                        e.preventDefault();
                        updateResults();
                    }
                });
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    updateResults();
                });
            }

            if (favoriteCheckbox) {
                favoriteCheckbox.addEventListener('change', function() {
                    updateResults();
                });
            }
        });

        // Funciones para gestión de carpetas del grupo
        function openEditFolderModal(folderId, folderName, parentId) {
            document.getElementById('editFolderModal_name').value = folderName;
            document.getElementById('editFolderModal_parent_id').value = parentId || '';
            document.getElementById('editFolderModalForm').action = `/groups/{{ $group->id }}/folders/${folderId}`;
            
            const parentSelect = document.getElementById('editFolderModal_parent_id');
            Array.from(parentSelect.options).forEach(option => {
                if (option.value == folderId) {
                    option.style.display = 'none';
                } else {
                    option.style.display = '';
                }
            });
            
            const modal = new bootstrap.Modal(document.getElementById('editFolderModal'));
            modal.show();
        }

        function deleteFolder(folderId, folderName) {
            const message = `¿Estás seguro de que deseas eliminar la carpeta "${folderName}"?\n\nLos items dentro de la carpeta NO se eliminarán, solo se les quitará la referencia a la carpeta.`;
            showConfirm({
                title: 'Eliminar carpeta',
                message: message,
                confirmText: 'Eliminar',
                confirmClass: 'btn-danger',
                onConfirm: () => doDeleteFolder(folderId),
            });
        }
        function doDeleteFolder(folderId) {
            fetch(`/groups/{{ $group->id }}/folders/${folderId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showAlert('Error', data.message || 'No se pudo eliminar la carpeta');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error', 'Error al eliminar la carpeta');
            });
        }

        // Manejar submit del formulario de crear carpeta
        document.getElementById('createFolderModalForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createFolderModal')).hide();
                    location.reload();
                } else {
                    showAlert('Error', data.message || 'No se pudo crear la carpeta');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error', 'Error al crear la carpeta');
            });
        });

        // Manejar submit del formulario de editar carpeta
        document.getElementById('editFolderModalForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editFolderModal')).hide();
                    location.reload();
                } else {
                    showAlert('Error', data.message || 'No se pudo actualizar la carpeta');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error', 'Error al actualizar la carpeta');
            });
        });
    </script>
    @endpush
</x-app-layout>
