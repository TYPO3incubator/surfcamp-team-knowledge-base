/**
 * Knowledge Base — search test panel
 * Handles keyword / semantic / RAG modes against the unified searchAction endpoint.
 */

const DEBOUNCE_MS = 400;

class KnowledgeBaseSearch {
    constructor(panel) {
        this.panel = panel;
        this.searchUrl = panel.dataset.searchUrl;
        this.mode = 'keyword';
        this.debounceTimer = null;
        this.abortController = null;

        this.input = panel.querySelector('[data-kb="input"]');
        this.button = panel.querySelector('[data-kb="btn"]');
        this.resultsEl = panel.querySelector('[data-kb="results"]');
        this.statusEl = panel.querySelector('[data-kb="status"]');
        this.modeButtons = panel.querySelectorAll('.kb-mode-btn');
        this.modeDescriptions = panel.querySelectorAll('[data-mode-desc]');

        if (!this.input || !this.button || !this.resultsEl || !this.statusEl) {
            console.error('KnowledgeBaseSearch: required elements not found', panel);
            return;
        }

        this.bindEvents();
    }

    bindEvents() {
        this.modeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.setMode(btn.dataset.mode));
        });

        this.input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                clearTimeout(this.debounceTimer);
                this.search();
            }
        });

        // Auto-search for keyword mode; manual-only for semantic/rag (slower)
        this.input.addEventListener('input', () => {
            if (this.mode !== 'keyword') return;
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.search(), DEBOUNCE_MS);
        });

        this.button.addEventListener('click', () => {
            clearTimeout(this.debounceTimer);
            this.search();
        });
    }

    setMode(mode) {
        this.mode = mode;

        this.modeButtons.forEach(btn => {
            const active = btn.dataset.mode === mode;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', String(active));
        });

        this.modeDescriptions.forEach(el => {
            el.hidden = el.dataset.modeDesc !== mode;
        });

        this.clearResults();
        this.input.focus();
    }

    async search() {
        const query = this.input.value.trim();

        if (query === '' && this.mode !== 'keyword') {
            this.showStatus('Enter a query to search.', 'info');
            return;
        }

        // Cancel any in-flight request
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();

        this.showStatus('Searching…', 'loading');
        this.button.disabled = true;

        const url = this.buildUrl(query, this.mode);

        try {
            const response = await fetch(url, {
                signal: this.abortController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.error) {
                this.showStatus(`Error: ${data.error}`, 'error');
                this.clearResults();
                return;
            }

            this.renderResults(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            this.showStatus(`Request failed: ${err.message}`, 'error');
        } finally {
            this.button.disabled = false;
        }
    }

    buildUrl(query, mode) {
        return this.searchUrl
            .replace('PLACEHOLDER_QUERY', encodeURIComponent(query))
            .replace('PLACEHOLDER_MODE', encodeURIComponent(mode));
    }

    renderResults(data) {
        const { mode, results, answer } = data;
        const count = results?.length ?? 0;

        let html = '';

        // Answer box (RAG only)
        if (answer !== null && answer !== undefined) {
            html += `
                <div class="kb-answer-box">
                    <div class="kb-answer-header">
                        <span class="kb-answer-icon">✦</span>
                        <span class="kb-answer-label">AI Answer</span>
                    </div>
                    <div class="kb-answer-body">${this.escapeHtml(answer)}</div>
                </div>`;
        }

        // Result count line
        const modeLabel = { keyword: 'keyword', semantic: 'semantic', rag: 'source' }[mode] ?? mode;
        const noun = count === 1 ? `${modeLabel} result` : `${modeLabel} results`;
        html += `<div class="kb-result-count">${count} ${noun}</div>`;

        if (count === 0) {
            html += `<div class="kb-no-results">No documents found.</div>`;
        } else {
            html += `<ul class="kb-result-list">`;
            for (const doc of results) {
                const score = doc.score !== null && doc.score !== undefined
                    ? `<span class="kb-score-badge" title="Cosine similarity">${(doc.score * 100).toFixed(0)}%</span>`
                    : '';
                const type = doc.type ? `<span class="kb-doc-type">${this.escapeHtml(doc.type)}</span>` : '';
                html += `
                    <li class="kb-result-item">
                        <div class="kb-result-main">
                            <span class="kb-result-headline">${this.escapeHtml(doc.headline ?? '(untitled)')}</span>
                            ${score}
                        </div>
                        <div class="kb-result-meta">
                            <span class="kb-doc-uid text-muted">#${doc.uid}</span>
                            ${type}
                        </div>
                    </li>`;
            }
            html += `</ul>`;
        }

        this.resultsEl.innerHTML = html;
        this.hideStatus();
    }

    showStatus(message, type) {
        this.statusEl.textContent = message;
        this.statusEl.dataset.type = type;
        this.statusEl.hidden = false;
    }

    hideStatus() {
        this.statusEl.hidden = true;
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

// Attach to any panel not yet initialised (guard with data attribute to avoid double-init).
function initPanels() {
    document.querySelectorAll('.kb-search-panel[data-search-url]:not([data-kb-ready])').forEach(panel => {
        panel.dataset.kbReady = '1';
        new KnowledgeBaseSearch(panel);
    });

    document.querySelectorAll('.kb-reindex-panel[data-reindex-url]:not([data-kb-ready])').forEach(panel => {
        panel.dataset.kbReady = '1';
        const btn = panel.querySelector('[data-kb-reindex="btn"]');
        const statusEl = panel.querySelector('[data-kb-reindex="status"]');
        if (!btn || !statusEl) return;

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            statusEl.textContent = 'Re-indexing…';
            statusEl.dataset.type = 'loading';
            statusEl.hidden = false;

            try {
                const response = await fetch(panel.dataset.reindexUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                statusEl.textContent = `Done — ${data.reindexed} document${data.reindexed === 1 ? '' : 's'} indexed.`;
                statusEl.dataset.type = 'success';
            } catch (err) {
                statusEl.textContent = `Failed: ${err.message}`;
                statusEl.dataset.type = 'error';
            } finally {
                btn.disabled = false;
            }
        });
    });
}

// Run immediately (covers full-page/iframe load where DOM is already ready).
initPanels();

// Also watch for content injected later via TYPO3's SPA module router.
const _observer = new MutationObserver(initPanels);
_observer.observe(document.documentElement, { childList: true, subtree: true });
