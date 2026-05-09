<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-bell text-primary"></i>
                <span>Notificaciones</span>
            </h2>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-check-all"></i>
                        <span>Marcar todas como leídas</span>
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="{{ route('notifications.index', ['unread_only' => false]) }}" 
                   class="btn btn-sm {{ !$unreadOnly ? 'btn-primary' : 'btn-outline-primary' }}">
                    Todas
                </a>
                <a href="{{ route('notifications.index', ['unread_only' => true]) }}" 
                   class="btn btn-sm {{ $unreadOnly ? 'btn-primary' : 'btn-outline-primary' }}">
                    No leídas
                    @if($unreadCount > 0)
                        <span class="badge bg-light text-dark ms-1">{{ $unreadCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Notificaciones Agrupadas -->
    @if($groupedNotifications->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash text-body-secondary" style="font-size: 3rem;"></i>
                <p class="text-body-secondary mt-3 mb-0">No hay notificaciones</p>
            </div>
        </div>
    @else
        @foreach($groupedNotifications as $group => $notifications)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-secondary border-0">
                    <h6 class="mb-0 fw-semibold text-body-secondary">{{ $group }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <div class="list-group-item {{ !$notification->read ? 'bg-body-secondary' : '' }}">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0">
                                        @php
                                            $icon = match($notification->type) {
                                                'group_invitation', 'group_invitation_accepted', 'group_invitation_rejected' => 'bi-people',
                                                'group_access_request', 'group_access_request_accepted', 'group_access_request_rejected' => 'bi-person-plus',
                                                'item_shared_user', 'item_shared_group', 'item_created_in_group' => 'bi-share',
                                                default => 'bi-bell',
                                            };
                                            $iconColor = match($notification->type) {
                                                'group_invitation', 'group_access_request' => 'text-primary',
                                                'group_invitation_accepted', 'group_access_request_accepted' => 'text-success',
                                                'group_invitation_rejected', 'group_access_request_rejected' => 'text-danger',
                                                'item_shared_user', 'item_shared_group', 'item_created_in_group' => 'text-info',
                                                default => 'text-secondary',
                                            };
                                        @endphp
                                        <i class="bi {{ $icon }} {{ $iconColor }} fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $notification->title }}</h6>
                                                <p class="text-body-secondary mb-1 small">{{ $notification->message }}</p>
                                                <small class="text-body-secondary">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <div class="d-flex align-items-start gap-2">
                                                @if(!$notification->read)
                                                    <span class="badge bg-primary rounded-pill" style="min-width: 8px; height: 8px;"></span>
                                                @endif
                                                @if(!$notification->read)
                                                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        @if($notification->type === 'group_invitation' && isset($notification->data['group_id']))
                                            <div class="mt-2 d-flex gap-2">
                                                <form id="form-accept-invitation-page-{{ $notification->id }}" 
                                                      method="POST" 
                                                      action="{{ route('groups.invitations.accept', $notification->data['group_id']) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modal-accept-invitation-page-{{ $notification->id }}">
                                                        <i class="bi bi-check-circle"></i> Aceptar
                                                    </button>
                                                </form>
                                                <x-confirm-modal
                                                    id="modal-accept-invitation-page-{{ $notification->id }}"
                                                    form-id="form-accept-invitation-page-{{ $notification->id }}"
                                                    title="Aceptar invitación"
                                                    message="¿Aceptar la invitación al grupo?"
                                                    confirm-text="Aceptar"
                                                    confirm-class="btn-success"
                                                />
                                                <form id="form-reject-invitation-page-{{ $notification->id }}" 
                                                      method="POST" 
                                                      action="{{ route('groups.invitations.reject', $notification->data['group_id']) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modal-reject-invitation-page-{{ $notification->id }}">
                                                        <i class="bi bi-x-circle"></i> Rechazar
                                                    </button>
                                                </form>
                                                <x-confirm-modal
                                                    id="modal-reject-invitation-page-{{ $notification->id }}"
                                                    form-id="form-reject-invitation-page-{{ $notification->id }}"
                                                    title="Rechazar invitación"
                                                    message="¿Rechazar la invitación al grupo?"
                                                    confirm-text="Rechazar"
                                                    confirm-class="btn-danger"
                                                />
                                            </div>
                                        @elseif($notification->action_url && $notification->action_label)
                                            <div class="mt-2">
                                                <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                                    {{ $notification->action_label }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</x-app-layout>
