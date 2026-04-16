function initBoard() {
    const button = document.getElementById('kb-edit-modal');
    const overlay = document.querySelector('.kb-edit-task-modal-content');
    const box = document.querySelector('.kb-edit-task-modal-box');
    const closeBtn = document.querySelector('.kb-edit-close');

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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBoard);
} else {
    initBoard();
}

