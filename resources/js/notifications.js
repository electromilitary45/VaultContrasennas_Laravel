/**
 * Módulo de Notificaciones en Tiempo Real
 * 
 * Maneja la actualización del contador y dropdown de notificaciones
 * cuando llegan nuevas notificaciones vía WebSocket.
 */

// Función para actualizar el contador de notificaciones
function updateNotificationCount() {
    if (!window.userId) return;

    fetch('/notifications/unread-count', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('#notificationsDropdown .badge');
        const count = data.count || 0;

        if (count > 0) {
            if (!badge) {
                // Crear badge si no existe
                const link = document.querySelector('#notificationsDropdown');
                if (link) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    newBadge.style.fontSize = '0.65rem';
                    newBadge.textContent = count > 99 ? '99+' : count;
                    link.appendChild(newBadge);
                }
            } else {
                badge.textContent = count > 99 ? '99+' : count;
            }
        } else {
            // Eliminar badge si no hay notificaciones
            if (badge) {
                badge.remove();
            }
        }
    })
    .catch(error => {
        console.error('Error al actualizar contador de notificaciones:', error);
    });
}

// Función para mostrar toast de notificación
function showNotificationToast(notification) {
    // Crear toast dinámico si no existe
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }

    // Crear toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    // Icono según tipo
    const icon = getNotificationIcon(notification.type);
    const iconColor = getNotificationIconColor(notification.type);

    toast.innerHTML = `
        <div class="toast-header">
            <i class="bi ${icon} ${iconColor} me-2"></i>
            <strong class="me-auto">${escapeHtml(notification.title)}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${escapeHtml(notification.message)}
            ${notification.action_url && notification.action_label ? `
                <div class="mt-2">
                    <a href="${notification.action_url}" class="btn btn-sm btn-primary">${escapeHtml(notification.action_label)}</a>
                </div>
            ` : ''}
        </div>
    `;

    toastContainer.appendChild(toast);

    // Inicializar y mostrar toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 6000, // 6 segundos
    });

    bsToast.show();

    // Eliminar toast del DOM después de ocultarse
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Función para obtener icono según tipo de notificación
function getNotificationIcon(type) {
    const icons = {
        'group_invitation': 'bi-people',
        'group_invitation_accepted': 'bi-people-fill',
        'group_invitation_rejected': 'bi-person-x',
        'group_access_request': 'bi-person-plus',
        'group_access_request_accepted': 'bi-person-check',
        'group_access_request_rejected': 'bi-person-x',
        'item_shared_user': 'bi-share',
        'item_shared_group': 'bi-share-fill',
        'item_created_in_group': 'bi-plus-circle',
    };
    return icons[type] || 'bi-bell';
}

// Función para obtener color de icono según tipo
function getNotificationIconColor(type) {
    const colors = {
        'group_invitation': 'text-primary',
        'group_access_request': 'text-primary',
        'group_invitation_accepted': 'text-success',
        'group_access_request_accepted': 'text-success',
        'group_invitation_rejected': 'text-danger',
        'group_access_request_rejected': 'text-danger',
        'item_shared_user': 'text-info',
        'item_shared_group': 'text-info',
        'item_created_in_group': 'text-info',
    };
    return colors[type] || 'text-secondary';
}

// Función para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Escuchar evento personalizado de notificación recibida
if (window.userId) {
    window.addEventListener('notification-received', (event) => {
        const notification = event.detail;
        
        // Mostrar toast
        showNotificationToast(notification);
        
        // Actualizar contador
        updateNotificationCount();
        
        // Si el dropdown está abierto, recargar (opcional: recargar página o hacer fetch)
        // Por simplicidad, solo actualizamos el contador
        // El usuario puede cerrar y abrir el dropdown para ver la nueva notificación
    });

    // Actualizar contador y acciones del dropdown al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        updateNotificationCount();
        setupNotificationDropdownActions();

        // Actualizar cada 30 segundos como fallback
        setInterval(updateNotificationCount, 30000);
    });
}

// Marcar una notificación como leída por AJAX y quitarla del dropdown
function setupNotificationDropdownActions() {
    const menu = document.getElementById('notificationsDropdownMenu');
    if (!menu) return;

    menu.querySelectorAll('.notification-mark-read-form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            const notificationId = form.dataset.notificationId;
            const url = form.action;
            const body = new FormData(form);

            fetch(url, {
                method: 'POST',
                body: body,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success !== true) return;
                removeNotificationItemFromDropdown(notificationId);
                updateNotificationCount();
            })
            .catch((err) => console.error('Error al marcar notificación como leída:', err));
        });
    });

    const markAllForm = document.querySelector('.notification-mark-all-read-form');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            const url = form.action;
            const body = new FormData(form);

            fetch(url, {
                method: 'POST',
                body: body,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success !== true) return;
                clearAllNotificationItemsInDropdown();
                updateNotificationCount();
            })
            .catch((err) => console.error('Error al marcar todas como leídas:', err));
        });
    }
}

function removeNotificationItemFromDropdown(notificationId) {
    const item = document.querySelector(`.notification-dropdown-item[data-notification-id="${notificationId}"]`);
    if (item) item.remove();
    const divider = document.querySelector(`.notification-dropdown-divider[data-after-notification-id="${notificationId}"]`);
    if (divider) divider.remove();
    showEmptyStateIfNoItems();
}

function clearAllNotificationItemsInDropdown() {
    const menu = document.getElementById('notificationsDropdownMenu');
    if (!menu) return;
    menu.querySelectorAll('.notification-dropdown-item, .notification-dropdown-divider').forEach((el) => el.remove());
    showEmptyStateIfNoItems();
    const headerForm = document.querySelector('.notification-mark-all-read-form');
    if (headerForm) headerForm.remove();
}

function showEmptyStateIfNoItems() {
    const emptyState = document.getElementById('notificationsDropdownEmptyState');
    if (!emptyState) return;
    const hasItems = document.querySelector('.notification-dropdown-item');
    if (!hasItems) emptyState.classList.remove('d-none');
}
