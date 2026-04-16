async function loadChildren(documentUid = 6) {
    const url = TYPO3.settings.ajaxUrls.loadDocumentChildren + '&documentUid=' + documentUid;

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error:', response.status, errorText);
            return null;
        }

        const data = await response.json();
        console.log('Children loaded:', data);

        return data;

    } catch (error) {
        console.error('Fetch error:', error);
        return null;
    }
}


// Render function
function renderChildren(children) {
    const todoColumn = document.querySelector('[data-column="todo"]');
    const progressColumn = document.querySelector('[data-column="progress"]');

    if (!todoColumn || !progressColumn) {
        console.warn('Columns not found');
        return;
    }

    todoColumn.innerHTML = '';
    progressColumn.innerHTML = '';

    children.forEach(child => {

        const card = document.createElement('div');
        card.className = 'board__card t3-page-ce-element';

        card.innerHTML = `
            <p class="board__card--title">${child.headline}</p>
            <p class="board__card--description">${child.markup ?? ''}</p>

            <div class="board__card--author">
                <span class="board__card--author-item">
                    <span class="board__card--name">U</span>
                    user ${child.user ?? ''}
                </span>
                <span>💬 0</span>
            </div>

            <span class="board__card-edit" data-uid="${child.uid}">✏️</span>
        `;

        // 👉 simple routing example (adjust later)
        if (child.status === 'in_progress') {
            progressColumn.appendChild(card);
        } else {
            todoColumn.appendChild(card);
        }
    });
}

// INIT (FIXED)
async function initBoard() {
    const children = await loadChildren(6);

    if (children && Array.isArray(children)) {
        renderChildren(children);
    }
}

// Safe bootstrap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBoard);
} else {
    initBoard();
}