document.addEventListener('DOMContentLoaded', async () => {
    const url = TYPO3.settings.ajaxUrls.loadDocumentChildren.concat('&documentUid=1')
    const response = await fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
    })
    const data = await response.json();
});

