function initCreateModal() {
    const button = document.getElementById('kb-open-modal');
    const overlay = document.querySelector('.kb-create-task-modal-content');
    const box = document.querySelector('.kb-create-task-modal-box');
    const closeBtn = document.querySelector('.kb-btn-close');

    if (!button || !overlay || !box) return;

    button.addEventListener('click', function () {
        overlay.classList.add('is-open');
    });

    // cancel button closes modal
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            overlay.classList.remove('is-open');
        });
    }

    // click outside closes
    overlay.addEventListener('click', function () {
        overlay.classList.remove('is-open');
    });

    // prevent inside click from closing
    box.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // ESC close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            overlay.classList.remove('is-open');
        }
    });
}

function initCreateTaskModal() {
    const form = document.getElementById('kb-creation-form-edit');
    if (!form) return;

    const button = form.querySelector('.kb-btn-create');
    const parentInput = form.querySelector('input[name="parentId"]');

    button.addEventListener('click', (e) => {
        e.preventDefault();

        const contentEl = document.querySelector('.kb-content');

        const parentId = contentEl?.dataset.openDocumentId;

        parentInput.value = parentId;

        form.submit();
    });
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initCreateModal();
        initCreateTaskModal();
    });
} else {
    initCreateModal();
    initCreateTaskModal();
}