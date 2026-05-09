<!-- Toast Container para Notificaciones -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-bell-fill text-primary me-2"></i>
            <strong class="me-auto" id="toastTitle">Notificación</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Mensaje se inserta aquí -->
        </div>
        @if(isset($actionUrl) && isset($actionLabel))
            <div class="toast-body border-top">
                <a href="{{ $actionUrl }}" class="btn btn-sm btn-primary">{{ $actionLabel }}</a>
            </div>
        @endif
    </div>
</div>
