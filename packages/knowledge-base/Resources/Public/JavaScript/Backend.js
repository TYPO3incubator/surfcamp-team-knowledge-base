document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('kb-open-modal');
    const overlay = document.querySelector('.kb-create-task-modal-content');
    const box = document.querySelector('.kb-create-task-modal-box');

    if (!button || !overlay || !box) return;

    button.addEventListener('click', function () {
        overlay.classList.add('is-open');
    });

    // click outside modal box closes
    overlay.addEventListener('click', function () {
        overlay.classList.remove('is-open');
    });

    // prevent closing when clicking inside modal
    box.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // ESC close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            overlay.classList.remove('is-open');
        }
    });
});

document.addEventListener('DOMContentLoaded', async () => {
    const url = TYPO3.settings.ajaxUrls.loadDocumentChildren.concat('&documentUid=1')
    const response = await fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
    })
    const data = await response.json();
});
