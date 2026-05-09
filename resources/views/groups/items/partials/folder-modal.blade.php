<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-light" id="{{ $modalId }}Label">
                    <i class="bi bi-folder text-primary"></i> {{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ $action }}" id="{{ $modalId }}Form">
                @csrf
                @if($method === 'PUT')
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="{{ $modalId }}_name" class="form-label fw-semibold">Nombre de la carpeta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="{{ $modalId }}_name" name="name" required 
                               placeholder="Ej: Documentos, Proyectos, etc.">
                    </div>

                    <div class="mb-3">
                        <label for="{{ $modalId }}_parent_id" class="form-label fw-semibold">Carpeta padre</label>
                        <select class="form-select" id="{{ $modalId }}_parent_id" name="parent_id">
                            <option value="">Sin carpeta padre (raíz)</option>
                            @if(isset($allFolders))
                                @php
                                    $excludeId = isset($excludeFolderId) ? $excludeFolderId : null;
                                @endphp
                                @foreach($allFolders as $folderOption)
                                    @if(!$excludeId || $folderOption->id != $excludeId)
                                        <option value="{{ $folderOption->id }}">{{ $folderOption->getFullPath() }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        <small class="text-body-secondary d-block mt-1">
                            <i class="bi bi-info-circle"></i> Opcional. Selecciona una carpeta padre para crear una subcarpeta.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
