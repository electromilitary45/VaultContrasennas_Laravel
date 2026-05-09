{{-- Modales globales para showAlert() y showConfirm() desde JS. Incluir en app y admin layout. --}}

{{-- Modal de aviso (alert): título + mensaje + OK --}}
<div class="modal fade" id="globalAlertModal" tabindex="-1" aria-labelledby="globalAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-light" id="globalAlertModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-body" id="globalAlertModalBody"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación (JS): título + mensaje + Cancelar + Confirmar; onConfirm vía JS --}}
<div class="modal fade" id="jsConfirmModal" tabindex="-1" aria-labelledby="jsConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-light" id="jsConfirmModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-body" id="jsConfirmModalBody" style="white-space: pre-line;"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="jsConfirmModalConfirm">Confirmar</button>
            </div>
        </div>
    </div>
</div>
