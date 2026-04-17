import { initBoardDragDrop } from './Board.js';

export async function loadChildren(documentUid = 6) {
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

        return data;

    } catch (error) {
        console.error('Fetch error:', error);
        return null;
    }
}

export async function loadBoardStatuses(documentUid) {
    const url = TYPO3.settings.ajaxUrls.loadBoardStatuses + '&documentUid=' + documentUid;

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) return [];

        return response.json();
    } catch (error) {
        console.error('Fetch error (statuses):', error);
        return [];
    }
}

export function renderStatuses(statuses) {
    const container = document.querySelector('.board__content-container');
    const addNewCol = container?.querySelector('.board__column-new')?.closest('.board__column');
    if (!container || !addNewCol) return;

    // Remove existing dynamic columns (keep only the Add New column)
    container.querySelectorAll('.board__column:not(:has(.board__column-new))').forEach(el => el.remove());

    statuses.forEach(status => {
        const col = document.createElement('div');
        col.className = 'board__column';
        col.innerHTML = `
            <div class="board__column--data">
                <h3 class="board__column-title">${status.title}</h3>
                <span class="board__column-items">0</span>
            </div>
            <div class="board__column-content t3-grid-cell" data-status-id="${status.uid}"></div>
        `;
        container.insertBefore(col, addNewCol);
    });
}

// Render function
export function renderChildren(children) {
    // Clear all status columns
    document.querySelectorAll('.board__column-content[data-status-id]').forEach(col => {
        col.innerHTML = '';
    });

    // First column is fallback for cards with no status
    const firstColumn = document.querySelector('.board__column-content[data-status-id]');

    children.forEach(child => {

        const name = child.userName ?? '';
        const firstLetter = name.charAt(0).toUpperCase();
        const card = document.createElement('div');
        card.className = 'board__card t3-page-ce-element';
        card.dataset.uid = child.uid;

        card.innerHTML = `
            <p class="board__card--title">${child.headline}</p>
            <p class="board__card--description">${child.markup ?? ''}</p>

            <div class="board__card--author">
                <span class="board__card--author-item"><span class="board__card--name"> ${firstLetter}</span>${child.userName ?? ''}</span>
                <span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
            </div>

            <div class="board__card-edit" data-uid="${child.uid}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/>
                </svg>
            </div>
        `;

        const targetColumn = child.statusId
            ? (document.querySelector(`.board__column-content[data-status-id="${child.statusId}"]`) ?? firstColumn)
            : firstColumn;

        targetColumn?.appendChild(card);
    });

    // Update item counts per column
    document.querySelectorAll('.board__column-content[data-status-id]').forEach(col => {
        const counter = col.closest('.board__column')?.querySelector('.board__column-items');
        if (counter) counter.textContent = col.querySelectorAll('.board__card').length;
    });

    initBoardDragDrop();
}
