<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2 text-primary"></i>
                <span>Dashboard</span>
        </h2>
            <a href="{{ route('vault.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo Item</span>
            </a>
        </div>
    </x-slot>

    @php
        $stats = $stats ?? [];
        $recentItems = $recentItems ?? collect();
        $favoriteItems = $favoriteItems ?? collect();
        $userGroups = $userGroups ?? ['owned' => collect(), 'member' => collect()];
        $recentShares = $recentShares ?? ['with_me' => collect(), 'by_me' => collect()];
    @endphp

    <!-- Estadísticas Principales -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary small mb-2 fw-normal">Total Items</h6>
                            <h3 class="mb-0 fw-light">{{ $stats['total_items'] ?? 0 }}</h3>
                            <small class="text-body-secondary">
                                {{ $stats['own_items'] ?? 0 }} propios
                            </small>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary small mb-2 fw-normal">Favoritos</h6>
                            <h3 class="mb-0 fw-light">{{ $stats['favorite_items'] ?? 0 }}</h3>
                            <small class="text-body-secondary">Items marcados</small>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary small mb-2 fw-normal">Compartidos</h6>
                            <h3 class="mb-0 fw-light">{{ ($stats['shared_with_me'] ?? 0) + ($stats['shared_by_me'] ?? 0) }}</h3>
                            <small class="text-body-secondary">
                                {{ $stats['shared_with_me'] ?? 0 }} recibidos, {{ $stats['shared_by_me'] ?? 0 }} enviados
                            </small>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-share-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-body-secondary small mb-2 fw-normal">Con TOTP</h6>
                            <h3 class="mb-0 fw-light">{{ $stats['items_with_totp'] ?? 0 }}</h3>
                            <small class="text-body-secondary">2FA habilitado</small>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda -->
        <div class="col-lg-8">
            <!-- Items Recientes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-light">
                        <i class="bi bi-clock-history text-primary"></i> Items Recientes
                    </h5>
                    <a href="{{ route('vault.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentItems->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentItems as $item)
                                <a href="{{ route('vault.show', $item) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                                            <div>
                                                @if($item->type === 'auth')
                                                    <i class="bi bi-key text-primary"></i>
                                                @elseif($item->type === 'card')
                                                    <i class="bi bi-credit-card text-info"></i>
                                                @elseif($item->type === 'note')
                                                    <i class="bi bi-sticky text-warning"></i>
                                                @elseif($item->type === 'api_key')
                                                    <i class="bi bi-code text-success"></i>
                                                @elseif($item->type === 'ssh_key')
                                                    <i class="bi bi-terminal text-danger"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong>{{ $item->title }}</strong>
                                                @if($item->has_totp)
                                                    <i class="bi bi-shield-check text-success ms-2" title="Tiene TOTP"></i>
                                                @endif
                                                @if($item->folder)
                                                    <small class="text-body-secondary d-block mt-1">
                                                        <i class="bi bi-folder"></i> {{ $item->folder->name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-body-secondary small">
                                            {{ $item->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-body-secondary py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No hay items recientes</p>
                            <a href="{{ route('vault.create') }}" class="btn btn-sm btn-primary mt-2">
                                Crear primer item
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Favoritos Destacados -->
            @if($favoriteItems->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-light">
                            <i class="bi bi-star-fill text-warning"></i> Favoritos
                        </h5>
                        <a href="{{ route('vault.index', ['favorite' => 1]) }}" class="btn btn-sm btn-outline-primary">
                            Ver todos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($favoriteItems as $item)
                                <a href="{{ route('vault.show', $item) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                                            <div>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong>{{ $item->title }}</strong>
                                                @if($item->has_totp)
                                                    <i class="bi bi-shield-check text-success ms-2" title="Tiene TOTP"></i>
                                                @endif
                                                @if($item->folder)
                                                    <small class="text-body-secondary d-block mt-1">
                                                        <i class="bi bi-folder"></i> {{ $item->folder->name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($item->type === 'auth')
                                                <span class="badge bg-primary">Auth</span>
                                            @elseif($item->type === 'card')
                                                <span class="badge bg-info">Card</span>
                                            @elseif($item->type === 'note')
                                                <span class="badge bg-warning">Note</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            <!-- Accesos Rápidos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body border-0">
                    <h5 class="mb-0 fw-light">
                        <i class="bi bi-lightning-fill text-warning"></i> Accesos Rápidos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vault.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-shield-lock"></i> Mi Vault
                        </a>
                        <a href="{{ route('groups.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-people"></i> Mis Grupos
                        </a>
                        <a href="{{ route('vault.index', ['favorite' => 1]) }}" class="btn btn-outline-warning">
                            <i class="bi bi-star"></i> Favoritos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Grupos -->
            @if($userGroups['owned']->count() > 0 || $userGroups['member']->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-light">
                            <i class="bi bi-people text-success"></i> Mis Grupos
                        </h5>
                        <a href="{{ route('groups.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if($userGroups['owned']->count() > 0)
                            <div class="px-3 pt-3">
                                <small class="text-body-secondary fw-semibold">Que Administro</small>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($userGroups['owned'] as $group)
                                    <a href="{{ route('groups.items.index', $group) }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $group->name }}</strong>
                                                <small class="text-body-secondary d-block">
                                                    <i class="bi bi-people"></i> {{ $group->active_members_count }} miembros
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">Owner</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($userGroups['member']->count() > 0)
                            <div class="px-3 pt-3">
                                <small class="text-body-secondary fw-semibold">Donde soy Miembro</small>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($userGroups['member'] as $group)
                                    <a href="{{ route('groups.items.index', $group) }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $group->name }}</strong>
                                                <small class="text-body-secondary d-block">
                                                    <i class="bi bi-people"></i> {{ $group->active_members_count }} miembros
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Estadísticas Adicionales -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h5 class="mb-0 fw-light">
                        <i class="bi bi-bar-chart text-info"></i> Organización
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-body-secondary">Carpetas</span>
                        <strong>{{ $stats['total_folders'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-body-secondary">Grupos (Owner)</span>
                        <strong>{{ $stats['owned_groups'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Grupos (Miembro)</span>
                        <strong>{{ $stats['member_groups'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
