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


// Render function
export function renderChildren(children) {
    const todoColumn = document.querySelector('[data-column="todo"]');
    const progressColumn = document.querySelector('[data-column="progress"]');

    if (!todoColumn || !progressColumn) {
        console.warn('Columns not found');
        return;
    }

    todoColumn.innerHTML = '';
    progressColumn.innerHTML = '';

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

            <button class="board__card-edit js-edit-task" data-uid="${child.uid}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/>
                </svg>
            </button>      
        `;

        if (child.status === 'in_progress') {
            if (child.statusId && !progressColumn.dataset.statusId) {
                progressColumn.dataset.statusId = child.statusId;
            }
            progressColumn.appendChild(card);
        } else {
            todoColumn.appendChild(card);
        }
    });

    initBoardDragDrop();
}

