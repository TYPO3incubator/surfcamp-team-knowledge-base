function createStatusModal() {
    const addNew = document.getElementById('status-open-modal');
    const overlay = document.querySelector('.kb-create-status-modal-content');
    const box = document.querySelector('.kb-create-status-modal-box');
    const closeBtn = document.querySelector('.kb-btn-close-status');

    if (!addNew || !overlay || !box) return;

    addNew.addEventListener('click', function () {
        overlay.classList.add('is-open');
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            overlay.classList.remove('is-open');
        });
    }

    overlay.addEventListener('click', function () {
        overlay.classList.remove('is-open');
    });

    box.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            overlay.classList.remove('is-open');
        }
    });
}

function initCreateStatusModal() {
    const form = document.getElementById('kb-status-creation-form-edit');
    if (!form) return;

    const button = form.querySelector('.kb-btn-create-status');
    const documentUidInput = form.querySelector('input[name="documentUid"]');
    if (!button || !documentUidInput) return;

    button.addEventListener('click', (e) => {
        e.preventDefault();

        const contentEl = document.querySelector('.kb-content');
        const documentUid = contentEl?.dataset.openDocumentId;
        documentUidInput.value = documentUid;

        form.submit();
    });
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        createStatusModal();
        initCreateStatusModal();
    });
} else {
    createStatusModal();
    initCreateStatusModal();
}