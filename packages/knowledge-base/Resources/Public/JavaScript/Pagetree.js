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

    // Use event delegation for document tree items
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.document-tree-item');
        if (item) {
            e.preventDefault();
            loadDocument(item.dataset.uid);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageTree);
} else {
    initPageTree();
}
