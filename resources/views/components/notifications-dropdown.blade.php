@php
    $user = Auth::user();
    $organizationId = $user->isPlatformSuperAdmin() ? null : $user->organization_id;
    $notificationService = app(\App\Services\NotificationService::class);
    $unreadCount = $notificationService->getUnreadCount($user, $organizationId);
    $notifications = $notificationService->getNotifications($user, $organizationId, limit: 10, unreadOnly: true);
@endphp

<li class="nav-item dropdown">
    <a class="nav-link position-relative d-flex align-items-center" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm notifications-dropdown-menu" aria-labelledby="notificationsDropdown" style="min-width: 350px; max-height: 500px; overflow-y: auto;" id="notificationsDropdownMenu">
        <li>
            <div class="dropdown-header d-flex align-items-center justify-content-between px-3 py-2">
                <span class="fw-semibold">Notificaciones</span>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline notification-mark-all-read-form">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;">
                            Marcar todas como leídas
                        </button>
                    </form>
                @endif
            </div>
        </li>
        <li><hr class="dropdown-divider my-0"></li>
        @if($notifications->isEmpty())
            <li id="notificationsDropdownEmptyState">
                <div class="px-3 py-4 text-center text-body-secondary">
                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                    <small>No hay notificaciones no leídas</small>
                </div>
            </li>
        @else
            @foreach($notifications as $notification)
                <li class="notification-dropdown-item" data-notification-id="{{ $notification->id }}">
                    <div class="dropdown-item-text px-3 py-2 {{ !$notification->read ? 'bg-body-secondary' : '' }}" style="cursor: pointer;">
                        <div class="d-flex align-items-start gap-2">
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
                                <i class="bi {{ $icon }} {{ $iconColor }} fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $notification->title }}</div>
                                        <div class="text-body-secondary small" style="font-size: 0.75rem;">{{ $notification->message }}</div>
                                        <div class="text-body-secondary" style="font-size: 0.65rem; margin-top: 0.25rem;">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if(!$notification->read)
                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.5rem; min-width: 8px; height: 8px;"></span>
                                    @endif
                                </div>
                                @if($notification->type === 'group_invitation' && isset($notification->data['group_id']))
                                    <div class="mt-2 d-flex gap-2">
                                        <form id="form-accept-invitation-dropdown-{{ $notification->id }}" 
                                              method="POST" 
                                              action="{{ route('groups.invitations.accept', $notification->data['group_id']) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="button" 
                                                    class="btn btn-sm btn-success" 
                                                    style="font-size: 0.75rem;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modal-accept-invitation-dropdown-{{ $notification->id }}">
                                                <i class="bi bi-check-circle"></i> Aceptar
                                            </button>
                                        </form>
                                        <x-confirm-modal
                                            id="modal-accept-invitation-dropdown-{{ $notification->id }}"
                                            form-id="form-accept-invitation-dropdown-{{ $notification->id }}"
                                            title="Aceptar invitación"
                                            message="¿Aceptar la invitación al grupo?"
                                            confirm-text="Aceptar"
                                            confirm-class="btn-success"
                                        />
                                        <form id="form-reject-invitation-dropdown-{{ $notification->id }}" 
                                              method="POST" 
                                              action="{{ route('groups.invitations.reject', $notification->data['group_id']) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    style="font-size: 0.75rem;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modal-reject-invitation-dropdown-{{ $notification->id }}">
                                                <i class="bi bi-x-circle"></i> Rechazar
                                            </button>
                                        </form>
                                        <x-confirm-modal
                                            id="modal-reject-invitation-dropdown-{{ $notification->id }}"
                                            form-id="form-reject-invitation-dropdown-{{ $notification->id }}"
                                            title="Rechazar invitación"
                                            message="¿Rechazar la invitación al grupo?"
                                            confirm-text="Rechazar"
                                            confirm-class="btn-danger"
                                        />
                                    </div>
                                @elseif($notification->action_url && $notification->action_label)
                                    <div class="mt-2">
                                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem;">
                                            {{ $notification->action_label }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if(!$notification->read)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="mt-2 notification-mark-read-form" data-notification-id="{{ $notification->id }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.7rem;">
                                    Marcar como leída
                                </button>
                            </form>
                        @endif
                    </div>
                </li>
                @if(!$loop->last)
                    <li class="notification-dropdown-divider" data-after-notification-id="{{ $notification->id }}"><hr class="dropdown-divider my-0"></li>
                @endif
            @endforeach
            <li id="notificationsDropdownEmptyState" class="d-none">
                <div class="px-3 py-4 text-center text-body-secondary">
                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                    <small>No hay notificaciones no leídas</small>
                </div>
            </li>
        @endif
        <li><hr class="dropdown-divider my-0"></li>
        <li>
            <a class="dropdown-item text-center py-2" href="{{ route('notifications.index') }}">
                <small class="text-body-secondary">Ver todas las notificaciones</small>
            </a>
        </li>
    </ul>
</li>
