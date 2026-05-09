@props([
    'id',
    'formId',
    'title' => 'Confirmar',
    'message',
    'confirmText' => 'Confirmar',
    'confirmClass' => 'btn-danger',
    'cancelText' => 'Cancelar',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-light" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-body">{{ $message }}</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
                <button type="submit" form="{{ $formId }}" class="btn {{ $confirmClass }}">{{ $confirmText }}</button>
            </div>
        </div>
    </div>
</div>
