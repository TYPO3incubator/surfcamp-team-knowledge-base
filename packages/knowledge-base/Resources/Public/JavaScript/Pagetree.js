function initTree() {
    document.querySelectorAll('.item-childs').forEach(el => { el.hidden = true; });
    document.querySelectorAll('.kb-btn-collapse').forEach(btn => { btn.classList.add('is-collapsed'); });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTree);
} else {
    initTree();
}

window.addEventListener('pageshow', initTree);

document.addEventListener('click', e => {
    const button = e.target.closest('.kb-btn-collapse');
    if (!button) return;

    e.stopPropagation();
    e.preventDefault();

    const wrapper = button.closest('.item-wrapper');
    const children = wrapper?.querySelector(':scope > .item-childs');

    if (children) {
        const isCollapsed = button.classList.toggle('is-collapsed');
        children.hidden = isCollapsed;
    }
});


function initPageTree() {
    const loadDocument = (uid) => {
        console.log("loading document with id " + uid)
        const url = TYPO3.settings.ajaxUrls.loadDocument.concat('&documentUid='+uid)

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.document) {
                    updatePageContent(data.document, data.commands);
                } else {
                    const errorMsg = data.message || 'Unknown error';
                    console.error('Error loading document:', errorMsg);
                    if (pageContentMarkup) {
                        pageContentMarkup.innerHTML = `<span style="color:red">Error loading document: ${errorMsg}</span>`;
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (pageContentMarkup) {
                    pageContentMarkup.innerHTML = `<span style="color:red">Fetch error: ${error.message}</span>`;
                }
            });
    };

    const pageContentMarkup = document.getElementById('page-content-markup');
    const pageContentHeadline = document.getElementById('page-content-headline');
    const pageContentCommands = document.getElementById('page-content-commands');
    const currentDocumentId = document.getElementById('open-document-id').innerHTML;

    loadDocument(currentDocumentId);

    /**
     * Updates the page content area with document data and available commands
     *
     * @param {Object} documentData
     * @param {Array} commands
     */
    const updatePageContent = (documentData, commands) => {
        if (pageContentHeadline) {
            pageContentHeadline.textContent = documentData.headline || '';
        }
        if (pageContentMarkup) {
            pageContentMarkup.innerHTML = documentData.markup || '<i>No content available</i>';
        }
        if (pageContentCommands) {
            pageContentCommands.innerHTML = '';
            if (commands && Array.isArray(commands)) {
                commands.forEach(cmd => {
                    const btn = document.createElement('button');
                    btn.className = 'basic-button';
                    btn.textContent = cmd.label;
                    btn.addEventListener('click', () => {
                        console.log(`Executing action "${cmd.name}" on document ${documentData.uid}`);
                    });
                    pageContentCommands.appendChild(btn);
                });
            }
        }
    };

    /**
     * Loads a document via AJAX
     *
     * @param {string} uid
     * @param {string} loadUrl
     */

    // Use event delegation for document tree items and search results
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.document-tree-item, .tree-search-result-item, .kb-flyout-result-item');
        if (item) {
            e.preventDefault();
            loadDocument(item.dataset.uid);
        }
    });

    // Search
    const searchInput = document.querySelector('.tree-search-input');
    const searchResultsEl = document.querySelector('.tree-search-results');
    const treeNodesEl = document.getElementById('tree-nodes-container');

    if (!searchInput || !searchResultsEl || !treeNodesEl) return;

    const iconPage = document.getElementById('kb-icon-page')?.innerHTML ?? '';
    const iconBoard = document.getElementById('kb-icon-board')?.innerHTML ?? '';

    let searchDebounce = null;
    let searchAbort = null;

    const escapeHtml = (str) =>
        String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

    const showTree = () => {
        searchResultsEl.hidden = true;
        searchResultsEl.innerHTML = '';
        treeNodesEl.hidden = false;
    };

    const renderSearchResults = (data) => {
        const { results } = data;
        const count = results?.length ?? 0;

        if (count === 0) {
            searchResultsEl.innerHTML = '<div class="tree-search-no-results">No documents found.</div>';
        } else {
            searchResultsEl.innerHTML = results.map(doc => `
                <div class="item tree-search-result-item" data-uid="${doc.uid}">
                    <span class="kb-collapse-placeholder"></span>
                    <span class="item-node-icon">${doc.type === 'board' ? iconBoard : iconPage}</span>
                    <div class="item-text">
                        <span>${escapeHtml(doc.headline ?? '(untitled)')}</span>
                    </div>
                </div>
            `).join('');
        }

        searchResultsEl.hidden = false;
        treeNodesEl.hidden = true;
    };

    const performSearch = async (query) => {
        if (!query) {
            showTree();
            return;
        }

        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();

        const url = TYPO3.settings.ajaxUrls.searchDocuments + '&query=' + encodeURIComponent(query) + '&mode=keyword';

        try {
            const response = await fetch(url, {
                signal: searchAbort.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            renderSearchResults(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error('Pagetree search error:', err);
        }
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => performSearch(searchInput.value.trim()), 400);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageTree);
} else {
    initPageTree();
}
