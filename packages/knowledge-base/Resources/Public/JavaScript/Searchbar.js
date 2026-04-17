/**
 * Knowledge Base — header search bar
 * Supports keyword (debounced auto-search), semantic, and RAG (Enter to search) modes.
 */

const SEARCHBAR_DEBOUNCE_MS = 400;

class KnowledgeBaseSearchBar {
    constructor(form) {
        this.form = form;
        this.wrapper = form.querySelector('.kb-search-wrapper');
        this.input = form.querySelector('.kb-search-input');
        this.flyout = form.querySelector('.kb-search-flyout');
        this.modeButtons = form.querySelectorAll('.kb-flyout-mode-btn');
        this.statusEl = form.querySelector('.kb-flyout-status');
        this.resultsEl = form.querySelector('.kb-flyout-results');

        if (!this.input || !this.flyout || !this.resultsEl) {
            console.error('KnowledgeBaseSearchBar: required elements missing', form);
            return;
        }

        this.mode = 'keyword';
        this.debounceTimer = null;
        this.abortController = null;
        this._closeTimer = null;

        // Reuse the pre-rendered icon markup from the Pagetree partial
        this.iconPage = document.getElementById('kb-icon-page')?.innerHTML ?? '';
        this.iconBoard = document.getElementById('kb-icon-board')?.innerHTML ?? '';

        this.bindEvents();
    }

    bindEvents() {
        // Keep flyout open whenever focus is inside the wrapper
        this.wrapper.addEventListener('focusin', () => {
            clearTimeout(this._closeTimer);
            this.openFlyout();
        });
        this.wrapper.addEventListener('focusout', () => {
            this._closeTimer = setTimeout(() => {
                if (!this.wrapper.contains(document.activeElement)) {
                    this.closeFlyout();
                }
            }, 0);
        });

        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeFlyout();
                this.input.blur();
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(this.debounceTimer);
                this.search();
            }
        });

        // Auto-search only in keyword mode
        this.input.addEventListener('input', () => {
            if (this.mode !== 'keyword') return;
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.search(), SEARCHBAR_DEBOUNCE_MS);
        });

        this.modeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.setMode(btn.dataset.mode);
                this.input.focus();
            });
        });

        this.form.addEventListener('submit', e => e.preventDefault());
    }

    openFlyout() {
        this.flyout.classList.add('is-open');
    }

    closeFlyout() {
        this.flyout.classList.remove('is-open');
    }

    setMode(mode) {
        this.mode = mode;
        this.modeButtons.forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.mode === mode);
        });
        this.clearResults();
    }

    async search() {
        const query = this.input.value.trim();

        if (!query) {
            this.clearResults();
            return;
        }

        if (this.abortController) this.abortController.abort();
        this.abortController = new AbortController();

        this.showStatus('Searching\u2026');

        const url = TYPO3.settings.ajaxUrls.searchDocuments
            + '&query=' + encodeURIComponent(query)
            + '&mode=' + encodeURIComponent(this.mode);

        try {
            const response = await fetch(url, {
                signal: this.abortController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();

            if (data.error) {
                this.showStatus(data.error);
                return;
            }

            this.renderResults(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            this.showStatus(`Error: ${err.message}`);
        }
    }

    renderResults(data) {
        const { results, answer } = data;
        const count = results?.length ?? 0;
        let html = '';

        if (answer) {
            html += `<div class="kb-flyout-answer">
                <span class="kb-flyout-answer-label">\u2736 AI Answer</span>
                <p class="kb-flyout-answer-body">${this.escapeHtml(answer)}</p>
            </div>`;
        }

        if (count === 0) {
            html += `<div class="kb-flyout-empty">No documents found.</div>`;
        } else {
            html += results.map(doc => {
                const icon = doc.type === 'board' ? this.iconBoard : this.iconPage;
                const crumbs = Array.isArray(doc.breadcrumb) ? doc.breadcrumb : [];
                const ancestors = crumbs.slice(0, -1);
                const path = ancestors.length
                    ? `<span class="kb-search-result-path">${ancestors.map(c => this.escapeHtml(c.headline)).join(' › ')}</span>`
                    : '';
                return `<a href="#" class="kb-search-result-item kb-flyout-result-item" data-uid="${doc.uid}">
                    ${icon}
                    <div class="kb-search-result-info">
                        <span class="kb-search-result-title">${this.escapeHtml(doc.headline ?? '(untitled)')}</span>
                        ${path}
                    </div>
                </a>`;
            }).join('');
        }

        this.resultsEl.innerHTML = html;
        this.hideStatus();
    }

    showStatus(message) {
        if (this.statusEl) {
            this.statusEl.textContent = message;
            this.statusEl.hidden = false;
        }
        this.resultsEl.innerHTML = '';
    }

    hideStatus() {
        if (this.statusEl) this.statusEl.hidden = true;
    }

    clearResults() {
        this.resultsEl.innerHTML = '';
        this.hideStatus();
    }

    escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}

function initSearchBars() {
    document.querySelectorAll('.kb-search-form:not([data-kb-ready])').forEach(form => {
        form.dataset.kbReady = '1';
        new KnowledgeBaseSearchBar(form);
    });
}

initSearchBars();

const _searchBarObserver = new MutationObserver(initSearchBars);
_searchBarObserver.observe(document.documentElement, { childList: true, subtree: true });
