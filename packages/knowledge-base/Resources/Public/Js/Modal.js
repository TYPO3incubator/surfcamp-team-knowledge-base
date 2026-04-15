document.addEventListener('click', function (e) {
    const plusButton = e.target.closest('.basic-button');
    const closeButton = e.target.closest('.t3js-modal-close');
    const actionItem = e.target.closest('.add-action');
    const closeFormButton = e.target.closest('.t3js-modal-close-form');

    const treeContainer = document.querySelector('.document-tree');
    const formModal = document.getElementById('form-modal');

    if (plusButton) {
        e.preventDefault();
        e.stopPropagation();
        const modal = plusButton.nextElementSibling || plusButton.parentElement.querySelector('.modal');
        if (modal) {
            modal.classList.add('is-visible');
            treeContainer.classList.add('has-modal');
        }
    }

    // OPEN SECOND FORM MODAL
    if (actionItem) {
        e.preventDefault();
        const type = actionItem.getAttribute('data-type');
        const parentId = actionItem.getAttribute('data-parent') || 0;

        actionItem.closest('.modal').classList.remove('is-visible');

        // Titel im Formular-Modal anpassen
        const titleEl = document.getElementById('form-modal-title');
        if (titleEl) titleEl.innerText = `Create New ${type.charAt(0).toUpperCase() + type.slice(1)}`;

        // Felder im f:form finden (Selektor sucht nach Namen, die auf [parentId] etc. enden)
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

    // CLOSE IF CLICKING OUTSIDE
    if (!e.target.closest('.modal') && !plusButton) {
        document.querySelectorAll('.modal.is-visible').forEach(m => m.classList.remove('is-visible'));
        treeContainer.classList.remove('has-modal');
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

// document.getElementById('kb-creation-form')?.addEventListener('submit', function(e) {
//     e.preventDefault();
//     const form = this;
//     const formData = new FormData(form);
//
//     const url = form.getAttribute('action');
//
//     fetch(url, {
//         method: 'POST',
//         body: formData,
//         headers: {
//             'X-Requested-With': 'XMLHttpRequest'
//         }
//     }).then(response => {
//         if (response.ok) {
//             window.location.reload();
//         } else {
//             alert('Error creating document');
//         }
//     });
// });