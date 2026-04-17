function initEditTaskModal() {
    const overlay = document.querySelector('#edit-task-modal');
    const closeBtn = document.querySelector('.kb-edit-close');

    if (!overlay) return;

    // OPEN modal when edit button clicked (event delegation)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-edit-task');
        if (!btn) return;

        const card = btn.closest('.board__card');
        if (!card) return;

        const uid = btn.dataset.uid;
        const title = card.querySelector('.board__card--title')?.textContent || '';
        const desc = card.querySelector('.board__card--description')?.textContent || '';

        // fill form
        const form = document.querySelector('#kb-edit-form-edit');
        if (form) {
            form.querySelector('input[name="documentUid"]').value = uid;
            form.querySelector('input[name="documentData[headline]"]').value = title;
            form.querySelector('textarea[name="documentData[markup]"]').value = desc;
        }

        // open modal
        overlay.classList.add('is-open');
    });

    // close modal
    closeBtn?.addEventListener('click', () => {
        overlay.classList.remove('is-open');
    });

    // click outside box closes
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('is-open');
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initEditTaskModal();
    });
} else {
    initEditTaskModal();
}