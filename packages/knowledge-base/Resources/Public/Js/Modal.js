document.addEventListener('click', function (e) {
    const plusButton = e.target.closest('.basic-button');
    const closeButton = e.target.closest('.t3js-modal-close');
    const actionItem = e.target.closest('.add-action');

    if (plusButton) {
        e.preventDefault();
        e.stopPropagation();

        const modal = plusButton.nextElementSibling || plusButton.parentElement.querySelector('.modal');
        if (modal) {
            modal.classList.toggle('is-visible');
            document.querySelector('.document-tree').classList.add('has-modal');
        }
    }

    if (closeButton || actionItem) {
        if (actionItem) e.preventDefault();

        const modal = e.target.closest('.modal');
        modal.classList.remove('is-visible');
        document.querySelector('.document-tree').classList.remove('has-modal');

        if (actionItem) {
            const type = actionItem.getAttribute('data-type');
            const parentId = actionItem.getAttribute('data-parent');
            console.log(`Action: ${type} for ID: ${parentId}`);
            alert(`🦄 Open ${type}-Modal (Parent ID: ${parentId})`);
        }
    }

    if (!e.target.closest('.modal') && !plusButton) {
        document.querySelectorAll('.modal.is-visible').forEach(m => m.classList.remove('is-visible'));
        document.querySelector('.document-tree').classList.remove('has-modal');
    }
});