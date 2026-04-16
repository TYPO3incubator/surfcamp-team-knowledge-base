document.addEventListener('click', function (e) {
    const plusButton = e.target.closest('.basic-button');
    const closeButton = e.target.closest('.t3js-modal-close, .t3js-modal-close-form');
    const actionItem = e.target.closest('.add-action');

    if (plusButton) {
        e.preventDefault();
        e.stopPropagation();
        const modal = plusButton.nextElementSibling;
        if (modal?.classList.contains('modal')) {
            closeAllModals();
            modal.classList.add('is-visible');
            updateOverlay();
        }
        return;
    }

    if (actionItem) {
        e.preventDefault();
        const type = actionItem.dataset.type;
        const parentId = actionItem.dataset.parent ?? 0;

        actionItem.closest('.modal')?.classList.remove('is-visible');

        const typeField = document.getElementById('field-type');
        const parentField = document.getElementById('field-parent');
        if (typeField) typeField.value = type;
        if (parentField) parentField.value = parentId;

        const titleEl = document.getElementById('form-modal-title');
        if (titleEl) titleEl.textContent = `Create New ${type.charAt(0).toUpperCase() + type.slice(1)}`;

        document.getElementById('form-modal')?.classList.add('is-visible');
        updateOverlay();
        return;
    }

    if (closeButton) {
        closeButton.closest('.modal')?.classList.remove('is-visible');
        updateOverlay();
        return;
    }

    if (!e.target.closest('.modal')) {
        closeAllModals();
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllModals();
    }
});

function closeAllModals() {
    document.querySelectorAll('.modal.is-visible').forEach(m => m.classList.remove('is-visible'));
    updateOverlay();
}

function updateOverlay() {
    const treeContainer = document.querySelector('.document-tree');
    if (!treeContainer) return;
    treeContainer.classList.toggle('has-modal', !!document.querySelector('.modal.is-visible'));
}
