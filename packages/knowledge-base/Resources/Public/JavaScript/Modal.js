document.addEventListener('click', function (e) {
    const plusButton = e.target.closest('.basic-button');
    const closeButton = e.target.closest('.t3js-modal-close');
    const actionItem = e.target.closest('.add-action');
    const closeFormButton = e.target.closest('.t3js-modal-close-form');

    const treeContainer = document.querySelector('.document-tree');
    const formModal = document.getElementById('form-modal');
    console.log(plusButton)
    if (plusButton) {
        e.preventDefault();
        e.stopPropagation();
        const modal = plusButton.nextElementSibling || plusButton.parentElement.querySelector('.modal');
        if (modal) {
            modal.classList.add('is-visible');
            treeContainer.classList.add('has-modal');
        }
    }

    if (actionItem) {
        e.preventDefault();
        const type = actionItem.getAttribute('data-type');
        const parentId = actionItem.getAttribute('data-parent') || 0;

        actionItem.closest('.modal').classList.remove('is-visible');

        const typeField = document.getElementById('field-type');
        const parentField = document.getElementById('field-parent');

        if (typeField) typeField.value = type;
        if (parentField) parentField.value = parentId;

        const titleEl = document.getElementById('form-modal-title');
        if (titleEl) titleEl.innerText = `Create New ${type.charAt(0).toUpperCase() + type.slice(1)}`;

        const typeInput = document.querySelector('input[name$="[type]"]');
        const parentInput = document.querySelector('input[name$="[parentId]"]');

        if (typeInput) typeInput.value = type;
        if (parentInput) parentInput.value = parentId;

        formModal.classList.add('is-visible');
        treeContainer.classList.add('has-modal');
    }

    if (closeButton || closeFormButton) {
        const modal = e.target.closest('.modal');
        modal.classList.remove('is-visible');
        treeContainer.classList.remove('has-modal');
    }

    if (!e.target.closest('.modal') && !plusButton) {
        document.querySelectorAll('.modal.is-visible').forEach(m => m.classList.remove('is-visible'));
        treeContainer?.classList.remove('has-modal');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        const activeModals = document.querySelectorAll('.modal.is-visible');

        if (activeModals.length > 0) {
            activeModals.forEach(modal => {
                modal.classList.remove('is-visible');
            });

            const treeContainer = document.querySelector('.document-tree');
            if (treeContainer) {
                treeContainer.classList.remove('has-modal');
            }
        }
    }
});