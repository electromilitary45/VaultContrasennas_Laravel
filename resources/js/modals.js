/**
 * Modales globales: showAlert y showConfirm.
 * Sustituyen alert() y confirm() nativos. Uso: showAlert('Título', 'Mensaje'), showConfirm({ title, message, onConfirm }).
 */

function initModals() {
    const alertEl = document.getElementById('globalAlertModal');
    const alertTitle = document.getElementById('globalAlertModalLabel');
    const alertBody = document.getElementById('globalAlertModalBody');

    const confirmEl = document.getElementById('jsConfirmModal');
    const confirmTitle = document.getElementById('jsConfirmModalLabel');
    const confirmBody = document.getElementById('jsConfirmModalBody');
    const confirmBtn = document.getElementById('jsConfirmModalConfirm');

    if (!alertEl || !confirmEl) return;

    let confirmCallback = null;
    let alertModalInstance = null;
    let confirmModalInstance = null;

    window.showAlert = function (title, message) {
        if (alertTitle) alertTitle.textContent = title || 'Aviso';
        if (alertBody) alertBody.textContent = message || '';
        if (!alertModalInstance) alertModalInstance = new bootstrap.Modal(alertEl);
        alertModalInstance.show();
    };

    window.showConfirm = function (opts) {
        const {
            title = 'Confirmar',
            message = '',
            confirmText = 'Confirmar',
            confirmClass = 'btn-danger',
            onConfirm,
        } = opts || {};

        if (confirmTitle) confirmTitle.textContent = title;
        if (confirmBody) {
            confirmBody.textContent = message;
            confirmBody.style.whiteSpace = 'pre-line';
        }
        if (confirmBtn) {
            confirmBtn.textContent = confirmText;
            confirmBtn.className = 'btn ' + confirmClass;
        }
        confirmCallback = typeof onConfirm === 'function' ? onConfirm : null;

        if (!confirmModalInstance) confirmModalInstance = new bootstrap.Modal(confirmEl);
        confirmModalInstance.show();
    };

    confirmBtn.addEventListener('click', function () {
        if (confirmCallback) {
            confirmCallback();
            confirmCallback = null;
        }
        if (confirmModalInstance) confirmModalInstance.hide();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModals);
} else {
    initModals();
}
