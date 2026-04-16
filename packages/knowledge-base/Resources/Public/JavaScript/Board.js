// Shared drag type so card and column handlers don't interfere with each other
let activeDragType = null;

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
            e.stopPropagation(); // prevent column dragstart from also firing
            activeDragType = 'card';
            draggedCard = this;
            placeholder = createPlaceholder(this);
            // Defer class addition so the browser captures the pre-fade snapshot
            setTimeout(() => this.classList.add('dragging'), 0);
        });

        card.addEventListener('dragend', function () {
            activeDragType = null;
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
            if (activeDragType !== 'card') return;
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
            if (activeDragType !== 'card') return;
            // Only remove highlight when leaving the column entirely
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });

        column.addEventListener('drop', function (e) {
            if (activeDragType !== 'card') return;
            e.preventDefault();
            this.classList.remove('drag-over');
            if (draggedCard && placeholder && placeholder.parentNode === this) {
                this.insertBefore(draggedCard, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
        });
    });
}

function initColumnDragDrop() {
    let draggedColumn = null;
    let placeholder = null;

    function createColumnPlaceholder() {
        const ph = document.createElement('div');
        ph.className = 'board__column--placeholder';
        return ph;
    }

    document.querySelectorAll('.board__column').forEach(function (column) {
        const header = column.querySelector('.board__column--data');
        if (!header) return;

        // Only enable dragging when the interaction starts from the column header
        header.addEventListener('mousedown', function () {
            column.setAttribute('draggable', 'true');

            function cleanup() {
                if (activeDragType !== 'column') {
                    column.removeAttribute('draggable');
                }
                document.removeEventListener('mouseup', cleanup);
            }
            document.addEventListener('mouseup', cleanup);
        });

        column.addEventListener('dragstart', function (e) {
            activeDragType = 'column';
            draggedColumn = this;
            placeholder = createColumnPlaceholder();
            // Defer class addition so the browser captures the pre-fade snapshot
            setTimeout(() => this.classList.add('column-dragging'), 0);
        });

        column.addEventListener('dragend', function () {
            activeDragType = null;
            this.classList.remove('column-dragging');
            this.removeAttribute('draggable');
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }
            draggedColumn = null;
            placeholder = null;
        });

        column.addEventListener('dragover', function (e) {
            if (activeDragType !== 'column') return;
            if (this === draggedColumn) return;
            e.preventDefault();

            const container = this.parentNode;
            const rect = this.getBoundingClientRect();
            const midX = rect.left + rect.width / 2;

            if (e.clientX < midX) {
                container.insertBefore(placeholder, this);
            } else {
                container.insertBefore(placeholder, this.nextSibling);
            }
        });

        column.addEventListener('drop', function (e) {
            if (activeDragType !== 'column') return;
            e.preventDefault();
            e.stopPropagation(); // prevent container drop from firing too
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedColumn, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
        });
    });

    // Allow drop on the placeholder itself (prevents "forbidden" cursor when hovering over it)
    const container = document.querySelector('.board__content-container');
    if (container) {
        container.addEventListener('dragover', function (e) {
            if (activeDragType === 'column') {
                e.preventDefault();
            }
        });

        container.addEventListener('drop', function (e) {
            if (activeDragType !== 'column') return;
            e.preventDefault();
            // placeholder.parentNode is already null if a column's drop handler ran first
            if (draggedColumn && placeholder && placeholder.parentNode) {
                placeholder.parentNode.insertBefore(draggedColumn, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initBoardDragDrop();
        initColumnDragDrop();
    });
} else {
    initBoardDragDrop();
    initColumnDragDrop();
}