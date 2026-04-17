import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';

// The TYPO3 CKEditor5 web component never stores the created editor on the
// element. Patch ClassicEditor.create here (both this module and the web
// component share the same singleton from @ckeditor/ckeditor5-editor-classic)
// so every created instance is captured, keyed by its source textarea.
const editorBySource = new WeakMap();
const _origCreate = ClassicEditor.create;
ClassicEditor.create = function (...args) {
    return _origCreate.apply(this, args).then(editor => {
        if (editor.sourceElement) {
            editorBySource.set(editor.sourceElement, editor);
        }
        return editor;
    });
};

let currentEditorTextarea = null;

/**
 * Creates a typo3-rte-ckeditor-ckeditor5 element, inserts it into #ckeditor-mount,
 * and sets the textarea value so CKEditor picks it up as initial content.
 */
function mountCkEditor(content) {
    const mount = document.getElementById('ckeditor-mount');
    if (!mount) return;

    const existing = mount.querySelector('typo3-rte-ckeditor-ckeditor5');
    if (existing) existing.remove();

    const options = mount.getAttribute('data-options') || '{}';

    const el = document.createElement('typo3-rte-ckeditor-ckeditor5');
    el.id = 'page-ckeditor';
    el.setAttribute('options', options);

    const textarea = document.createElement('textarea');
    textarea.setAttribute('slot', 'textarea');
    textarea.setAttribute('rows', '18');
    textarea.className = 'form-control';
    textarea.style.display = 'none';
    textarea.value = content;   // CKEditor reads this as initial data
    el.appendChild(textarea);

    // Insert into the now-visible form so CKEditor initialises with real dimensions
    mount.appendChild(el);
    currentEditorTextarea = textarea;
}

function unmountCkEditor() {
    const mount = document.getElementById('ckeditor-mount');
    const existing = mount?.querySelector('typo3-rte-ckeditor-ckeditor5');
    if (existing) existing.remove();
    currentEditorTextarea = null;
}

function enterEditMode() {
    const pageEl = document.querySelector('.page');
    if (!pageEl) return;

    const contentEl = document.querySelector('.kb-content');

    const currentUid        = contentEl?.dataset.openDocumentId    || '';
    const currentHeadline   = pageEl.dataset.currentHeadline       || '';
    const currentVisibility = pageEl.dataset.currentVisibility     || 'public';
    const currentMarkup     = pageEl.dataset.currentMarkup         || '';

    const uidField         = document.getElementById('edit-document-uid');
    const headlineInput    = document.getElementById('document-headline');
    const visibilitySelect = document.getElementById('document-visibility');
    if (uidField)         uidField.value         = currentUid;
    if (headlineInput)    headlineInput.value    = currentHeadline;
    if (visibilitySelect) visibilitySelect.value = currentVisibility;

    const markupEl = document.getElementById('page-content-markup');
    if (markupEl) markupEl.hidden = true;

    // Show the edit form first so CKEditor mounts into a visible, dimensioned container
    pageEl.classList.add('page-is-editing');

    mountCkEditor(currentMarkup);
}

function exitEditMode() {
    const pageEl = document.querySelector('.page');
    if (!pageEl) return;

    unmountCkEditor();

    const markupEl = document.getElementById('page-content-markup');
    if (markupEl) markupEl.hidden = false;

    pageEl.classList.remove('page-is-editing');
}

function initEditFunctionality() {
    document.addEventListener('click', (e) => {
        if (e.target.closest('.kb-btn-edit'))   { enterEditMode();  return; }
        if (e.target.closest('.kb-btn-cancel')) { exitEditMode(); }
    });

    const form = document.getElementById('kb-edit-form');
    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const markupField = document.getElementById('document-editor-markup');
            if (!markupField) return;

            const editor = currentEditorTextarea
                ? editorBySource.get(currentEditorTextarea)
                : null;
            //markupField.value = editor.getData();

            if (editor) {
                // Preferred path: getData() returns clean, normalised HTML
                //markupField.value = editor.getData();
                markupField.setAttribute('value', editor.getData())
            } else if (currentEditorTextarea) {
                // Fallback: CKEditor syncs content back via updateSourceElement()
                markupField.value = currentEditorTextarea.value;
            }
            form.submit();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditFunctionality);
} else {
    initEditFunctionality();
}
