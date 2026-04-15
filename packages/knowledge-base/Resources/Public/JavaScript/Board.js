function initBoard() {
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
}

function initBoardDragDrop() {
    let draggedCard = null;
    let placeholder = null;

    function createPlaceholder(card) {
        const ph = document.createElement('div');
        ph.className = 'board__card--placeholder';
        ph.style.height = card.offsetHeight + 'px';
        return ph;
    }

    // Returns the card element that comes after the given Y position in a column,
    // or undefined if the position is after all cards.
    function getCardAfterPosition(column, y) {
        const cards = [...column.querySelectorAll('.board__card:not(.dragging)')];
        return cards.reduce((closest, card) => {
            const box = card.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset, element: card };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    document.querySelectorAll('.board__card').forEach(function (card) {
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragstart', function (e) {
            draggedCard = this;
            placeholder = createPlaceholder(this);
            // Defer class addition so the browser captures the pre-fade snapshot
            setTimeout(() => this.classList.add('dragging'), 0);
        });

        card.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }
            document.querySelectorAll('.board__column-content').forEach(function (col) {
                col.classList.remove('drag-over');
            });
            draggedCard = null;
            placeholder = null;
        });
    });

    document.querySelectorAll('.board__column-content:not(.board__column-new)').forEach(function (column) {
        column.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('drag-over');

            const afterCard = getCardAfterPosition(this, e.clientY);
            if (afterCard) {
                this.insertBefore(placeholder, afterCard);
            } else {
                this.appendChild(placeholder);
            }
        });

        column.addEventListener('dragleave', function (e) {
            // Only remove highlight when leaving the column entirely
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });

        column.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            if (draggedCard && placeholder && placeholder.parentNode === this) {
                this.insertBefore(draggedCard, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initBoard();
        initBoardDragDrop();
    });
} else {
    initBoard();
    initBoardDragDrop();
}
