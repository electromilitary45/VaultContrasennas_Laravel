@php
    $isActive = ($activeFolderId ?? null) == $folder->id;
    $itemCount = $folder->vault_items_count ?? $folder->vaultItems->count() ?? 0;
    $hasChildren = $folder->children && $folder->children->count() > 0;
@endphp

<div class="list-group-item p-0">
    <div class="d-flex align-items-center">
        <a href="{{ route('vault.index', ['folder_id' => $folder->id] + request()->except('folder_id')) }}" 
           class="list-group-item-action flex-grow-1 {{ $isActive ? 'active' : '' }} d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none"
           style="margin-left: {{ $level * 20 }}px;">
            <span>
                <i class="bi bi-folder{{ $isActive ? '-fill' : '' }}"></i> 
                {{ $folder->name }}
            </span>
            <span class="badge bg-{{ $isActive ? 'light text-dark' : 'secondary' }} rounded-pill">{{ $itemCount }}</span>
        </a>
        <div class="dropdown">
            <button class="btn btn-sm btn-link text-body-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button class="dropdown-item" onclick="openEditFolderModal({{ $folder->id }}, '{{ $folder->name }}', {{ $folder->parent_id ?? 'null' }})">
                        <i class="bi bi-pencil"></i> Renombrar
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger" onclick="deleteFolder({{ $folder->id }}, '{{ $folder->name }}')">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </li>
            </ul>
        </div>
    </div>
    
    @if($hasChildren)
        @foreach($folder->children as $child)
            @include('vault.partials.folder-item', [
                'folder' => $child,
                'activeFolderId' => $activeFolderId ?? null,
                'level' => $level + 1
            ])
        @endforeach
    @endif
</div>
