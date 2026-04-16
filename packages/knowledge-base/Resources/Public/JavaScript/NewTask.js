function initCreateTaskModal() {
    const button = document.querySelector('.kb-btn-create');
    const nameInput = document.getElementById('task-name');

    button.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = TYPO3.settings.createDocumentUrl;

        const headline = document.createElement('input');
        headline.name = 'documentHeadline';
        headline.value = nameInput.value;

        const parent = document.createElement('input');
        parent.name = 'parentId';
        parent.value = 0;

        const type = document.createElement('input');
        type.name = 'type';
        type.value = 'normal';

        form.appendChild(headline);
        form.appendChild(parent);
        form.appendChild(type);

        document.body.appendChild(form);
        form.submit();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCreateTaskModal);
} else {
    initCreateTaskModal();
}