function initUpdateTask() {
    const button = document.querySelector('.kb-btn-save');

    if (!button) return;

    button.addEventListener('click', () => {
        const documentUid = button.dataset.uid;

        const headlineInput = document.getElementById('document-headline');
        const markupInput = document.getElementById('document-markup');
        const visibilityInput = document.getElementById('document-visibility');

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = TYPO3.settings.updateDocumentUrl;

        // documentUid
        const uidField = document.createElement('input');
        uidField.name = 'documentUid';
        uidField.value = documentUid;

        // headline
        const headline = document.createElement('input');
        headline.name = 'documentData[headline]';
        headline.value = headlineInput.value;

        // markup
        const markup = document.createElement('input');
        markup.name = 'documentData[markup]';
        markup.value = markupInput.value;

        // visibility
        const visibility = document.createElement('input');
        visibility.name = 'documentData[visibility]';
        visibility.value = visibilityInput.value;

        form.appendChild(uidField);
        form.appendChild(headline);
        form.appendChild(markup);
        form.appendChild(visibility);

        document.body.appendChild(form);
        form.submit();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUpdateTask);
} else {
    initUpdateTask();
}