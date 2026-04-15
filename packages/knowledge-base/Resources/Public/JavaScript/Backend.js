document.addEventListener('DOMContentLoaded', () => {
    const pageContentMarkup = document.getElementById('page-content-markup');
    const pageContentHeadline = document.getElementById('page-content-headline');
    const pageContentCommands = document.getElementById('page-content-commands');

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
    const loadDocument = (uid) => {

        const url = TYPO3.settings.ajaxUrls.loadDocument.concat('&documentUid='+uid)

        fetch(url.toString())
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

    // Use event delegation for document tree items
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.document-tree-item');
        if (item) {
            e.preventDefault();
            loadDocument(item.dataset.uid);
        }
    });
});
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
