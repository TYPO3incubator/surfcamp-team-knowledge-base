INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (39, 0, 1776433858, 1776433858, 0, 0, 'User Guide: Mastering the Knowledge Base', '<h3>Welcome to your Knowledge Hub!</h3>
<p>This extension is designed to be the central brain for our team. Here is how you can get the most out of it.</p>
<h4>Navigating the Tree</h4>
<p>On the left, you see the document tree. You can expand and collapse folders to explore different categories. Clicking a document will load its content instantly.</p>
<h4>Creating Content</h4>
<ul>
  <li><strong>Normal Documents:</strong> Use these for text-heavy information, guides, and documentation.</li>
  <li><strong>Board Documents:</strong> Use these for task management and workflows. They allow you to define statuses and drag tasks between them.</li>
</ul>
<h4>Collaboration</h4>
<p>Every document supports team interaction. You can leave <strong>Comments</strong> to discuss content or use <strong>Reactions</strong> to show your support or acknowledgment.</p>', 'normal', 'public', 0, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (40, 0, 1776433858, 1776433858, 0, 0, 'Working with Boards', '<h3>Task Management with Boards</h3>
<p>Boards are a powerful way to visualize workflows. Each board can have multiple columns representing different statuses.</p>
<h4>Key Features</h4>
<ul>
  <li><strong>Drag and Drop:</strong> Move tasks between columns effortlessly to update their status.</li>
  <li><strong>Column Reordering:</strong> You can also drag the columns themselves to rearrange your workflow.</li>
  <li><strong>Custom Statuses:</strong> Create and rename statuses to fit your team\'s specific needs.</li>
</ul>', 'normal', 'public', 39, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (41, 0, 1776433858, 1776433858, 0, 0, 'Visibility and Privacy', '<h3>Control Who Sees What</h3>
<p>Each document has a visibility setting:</p>
<ul>
  <li><strong>Public:</strong> Visible to everyone in the team. Best for finalized guides and shared info.</li>
  <li><strong>Private:</strong> Only visible to you (the creator). Use this for drafts or personal notes.</li>
</ul>
<p>You can toggle this setting in the edit mode of any document.</p>', 'normal', 'public', 39, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (42, 0, 1776433858, 1776433858, 0, 0, 'Search & AI: Finding Answers Fast', '<h3>Three Ways to Search</h3>
<p>We provide multiple search modes to ensure you always find what you need.</p>
<h4>1. Keyword Search (Fulltext)</h4>
<p>The traditional way. Type keywords, and we match them against headlines and document content using MySQL Fulltext indexing.</p>
<h4>2. Semantic Search (Smart Search)</h4>
<p>Uses AI to understand the meaning behind your query. Even if you don\'t use the exact words, it finds relevant concepts.</p>
<h4>3. AI Assistant (RAG)</h4>
<p>The most advanced mode. Type a question like "How do I create a board?", and the AI will read relevant documents and generate a direct answer for you.</p>', 'normal', 'public', 0, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (43, 0, 1776433858, 1776433858, 0, 0, 'How Semantic Search Works', '<h3>The Power of Embeddings</h3>
<p>When you save a document, the system generates a mathematical representation of its meaning called an <strong>Embedding</strong>.</p>
<p>When you search, your query is also converted into a vector. We then calculate the <strong>Cosine Similarity</strong> between your query and all documents to find the best match.</p>', 'normal', 'public', 42, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (44, 0, 1776433858, 1776433858, 0, 0, 'Asking the AI (RAG)', '<h3>Retrieval-Augmented Generation</h3>
<p>RAG stands for Retrieval-Augmented Generation. Here is what happens when you "Ask":</p>
<ol>
  <li><strong>Retrieve:</strong> We find the most relevant document snippets semantically.</li>
  <li><strong>Augment:</strong> We feed these snippets as context into a Large Language Model (LLM).</li>
  <li><strong>Generate:</strong> The LLM generates a human-like answer based <em>only</em> on our knowledge base.</li>
</ol>
<p>This reduces "hallucinations" and ensures the answers are relevant to our team.</p>', 'normal', 'public', 42, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (45, 0, 1776433858, 1776433858, 0, 0, 'Technical Documentation: Under the Hood', '<h3>Architecture Overview</h3>
<p>The system is built on TYPO3 v14 and split into two main packages:</p>
<ul>
  <li><code>knowledge-base</code>: Handles UI, data persistence, and the document tree.</li>
  <li><code>smart-search</code>: Provides the AI/ML integration layer.</li>
</ul>', 'normal', 'public', 0, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (48, 1, 1776434295, 1776434295, 0, 0, 'Data Models', '
<h3>Knowledge Base Entities</h3>
<p>The core of the extension is built around a few central Extbase domain models that define the structure of the knowledge base and its collaboration features.</p>

<h4>1. Document</h4>
<p>The <b>Document</b> entity is the central piece of the package. It can represent a standard article or a board container.</p>
<ul>
    <li><b>headline</b>: The title of the article or board.</li>
    <li><b>markup</b>: The main HTML content, rendered using a rich text editor.</li>
    <li><b>type</b>: Defines if it is a "normal" article or a "board" view.</li>
    <li><b>visibility</b>: Can be "public" (viewable by everyone) or "private" (only viewable by the author).</li>
    <li><b>parent</b>: A self-referential property that enables hierarchical document trees.</li>
    <li><b>status</b>: Links a document to a board column when the document is treated as a task.</li>
</ul>

<h4>2. Status</h4>
<p>The <b>Status</b> entity represents columns in a Board view. It allows for horizontal task management within a board.</p>
<ul>
    <li><b>title</b>: The name of the board column (e.g., "In Progress").</li>
    <li><b>document</b>: The parent board that this status belongs to.</li>
    <li><b>ordering</b>: An integer used to determine the horizontal order of columns.</li>
</ul>

<h4>3. Collaboration (Comment & Reaction)</h4>
<p>Two supporting entities enable team interaction:</p>
<ul>
    <li><b>Comment</b>: Stores user-generated feedback linked to a specific document.</li>
    <li><b>Reaction</b>: Stores quick emoji-style feedback (Like, Heart, Thumbs Down, Celebrate) from backend users.</li>
</ul>
', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (49, 1, 1776434295, 1776434295, 0, 0, 'Actions', '
<h3>Controller Actions & Workflows</h3>
<p>The business logic is orchestrated by the <code>BackendKnowledgeBaseController</code>, which handles both standard HTTP requests and modern AJAX-driven interactions.</p>

<h4>1. Navigation & State</h4>
<ul>
    <li><b>indexAction</b>: Bootstraps the main interface, assigning the initial tree structure and the current active document to the view.</li>
    <li><b>ajaxLoadDocumentAction</b>: Returns a JSON representation of a document for seamless content switching in the UI.</li>
    <li><b>ajaxLoadDocumentChildrenAction</b>: Used for lazy-loading child nodes in the sidebar tree.</li>
</ul>

<h4>2. Content Management</h4>
<ul>
    <li><b>createAction / updateAction</b>: Handles the lifecycle of articles, including automatic re-indexing for AI search when content is updated.</li>
    <li><b>createStatusAction / ajaxUpdateStatusAction</b>: Enables dynamic management of board columns and their sorting.</li>
</ul>

<h4>3. Search & AI Integration</h4>
<ul>
    <li><b>ajaxSearchAction</b>: The central routing point for searches. It delegates the query to the <code>SearchService</code> which supports:
        <ul>
            <li><b>Keyword Mode</b>: MySQL FULLTEXT search.</li>
            <li><b>Semantic Mode</b>: AI-powered vector similarity search.</li>
            <li><b>RAG Mode</b>: LLM-generated answers based on document context.</li>
        </ul>
    </li>
    <li><b>reindexAction</b>: A management action to manually trigger the generation of embeddings for all documents in the system.</li>
</ul>
', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (50, 1, 1776434295, 1776434295, 0, 0, 'DTOs', '
<h3>Data Transfer Objects</h3>
<p>To keep the application performance high, we use specialized Data Transfer Objects (DTOs) for specific parts of the system where full Extbase model loading is unnecessary.</p>

<h4>SlimDocumentDto</h4>
<p>The <code>SlimDocumentDto</code> is the most important DTO in the package. It provides a lightweight view of document metadata.</p>
<ul>
    <li><b>Usage</b>: Primarily used by the <code>DocumentTreeService</code> to build the navigation sidebar.</li>
    <li><b>Benefits</b>: By only fetching <code>uid</code>, <code>parent</code>, <code>visibility</code>, <code>type</code>, and <code>headline</code>, we avoid loading the often large <code>markup</code> field from the database, significantly reducing memory usage during recursive tree builds.</li>
    <li><b>Persistence</b>: Mapped directly from database rows in the <code>DocumentRepository</code> via a custom Extbase persistence class map.</li>
</ul>
', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (51, 1, 1776434295, 1776434295, 0, 0, 'Services', '
<h3>Service-Oriented Architecture</h3>
<p>The extension follows a clean separation of concerns by offloading complex logic into dedicated services.</p>

<h4>1. Content & Tree Services</h4>
<ul>
    <li><b>DocumentTreeService</b>: Logic for building the recursive hierarchy for the sidebar navigation.</li>
    <li><b>DocumentService</b>: Core CRUD operations and search coordination.</li>
    <li><b>StatusService</b>: Logic for managing board columns and their horizontal ordering.</li>
</ul>

<h4>2. Smart Search Pipeline</h4>
<ul>
    <li><b>RagService</b>: The orchestrator for the "Ask AI" feature. It builds the retrieval context and interacts with the generation services.</li>
    <li><b>EmbeddingService</b>: A bridge service that prepares document content for vectorization and delegates to <code>DocumentEmbeddingAdapter</code>.</li>
    <li><b>SearchService</b>: Normalizes results from different search implementations (MySQL vs. Vector) into a unified format for the UI and implements the fallback chain.</li>
</ul>

<h4>3. Low-level AI Logic (Smart Search Package)</h4>
<ul>
    <li><b>VectorService</b>: Implements the mathematical logic for Cosine Similarity used in semantic matching, plus hash-based change detection to avoid redundant embedding calls.</li>
    <li><b>ModelAvailabilityService</b>: Checks the health of external AI inference servers (Llama.cpp) before attempting AI operations. Results are cached per request.</li>
    <li><b>GenerationService</b>: Manages the prompt engineering and API calls for LLM text generation via the OpenAI-compatible <code>/v1/chat/completions</code> endpoint.</li>
</ul>
', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (52, 0, 1776438710, 1776434697, 1, 0, 'aaaa', '', 'page', 'public', 40, '3', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (53, 1, 1776500000, 1776500000, 0, 0, 'Configuration & AI Server Settings', '<h3>Extension Settings (Install Tool)</h3>
                                                                                                                                                                    <p>All AI-related configuration lives in the TYPO3 Install Tool under <b>Admin Tools → Settings → Extension Configuration → smart_search</b>. The values are read at runtime by <code>SmartSearchConfiguration</code>.</p>

                                                                                                                                                                    <h4>Server Endpoints</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><b>embeddingServerUrl</b> (default: <code>http://localhost:8080</code>) — The URL of the llama.cpp server running in embedding mode (<code>llama-server --embedding</code>). Requests are POSTed to <code>{url}/embedding</code> with JSON body <code>{"content": "text"}</code>.</li>
                                                                                                                                                                      <li><b>generationServerUrl</b> (default: <code>http://localhost:8081</code>) — The URL of the llama.cpp server running in chat/generation mode. Requests go to the OpenAI-compatible <code>{url}/v1/chat/completions</code> endpoint.</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <h4>Generation Settings</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><b>generationMaxTokens</b> (default: <code>512</code>) — Maximum number of tokens the LLM may generate per answer. Increase for longer responses; reduce to cut latency.</li>
                                                                                                                                                                      <li><b>generationTimeout</b> (default: <code>300</code> seconds) — HTTP timeout for generation requests. LLMs running on CPU can be slow; 5 minutes is a conservative default.</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <h4>Embedding & RAG Tuning</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><b>embeddingContextLength</b> (default: <code>6000</code> chars) — Text is truncated to this length before being sent to the embedding server. The <code>LlamaCppEmbeddingClient</code> also halves this on HTTP 400 errors (up to 4 retries) to handle model context-length overruns.</li>
                                                                                                                                                                      <li><b>ragTopK</b> (default: <code>5</code>) — How many candidate documents are retrieved during semantic search before filtering by the similarity threshold.</li>
                                                                                                                                                                      <li><b>documentContextLength</b> (default: <code>800</code> chars) — Each document snippet passed to the LLM is truncated to this length to keep the prompt manageable.</li>
                                                                                                                                                                      <li><b>semanticThreshold</b> (default: <code>0.30</code>) — Minimum cosine similarity score for a document to be included in RAG context. Documents below this score are discarded; if none survive, the system falls back to fulltext search.</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <h4>Health Checks</h4>
                                                                                                                                                                    <p>Before any AI operation, <code>ModelAvailabilityService</code> sends a <code>GET {url}/health</code> request with a 2-second timeout to each server. Results are cached per request. If a server is unreachable for any reason, the corresponding feature is gracefully disabled.</p>', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (54, 1, 1776500000, 1776500000, 0, 0, 'Vector Storage & Embedding Pipeline', '<h3>How Vectors Are Stored</h3>
                                                                                                                                                                    <p>Embedding vectors are persisted in the <code>tx_smartsearch_vector</code> table, managed by <code>VectorRepository</code> in the <code>smart-search</code> package.</p>

                                                                                                                                                                    <h4>Table Schema</h4>
                                                                                                                                                                    <pre><code>tx_smartsearch_vector
                                                                                                                                                                      uid            INT UNSIGNED AUTO_INCREMENT
                                                                                                                                                                      collection     VARCHAR(255)   -- logical namespace, e.g. "knowledge-base-documents"
                                                                                                                                                                      identifier     VARCHAR(255)   -- the document UID as a string
                                                                                                                                                                      vector         LONGTEXT       -- JSON-encoded float[]
                                                                                                                                                                      content_hash   VARCHAR(32)    -- MD5 of the normalised source text
                                                                                                                                                                      tstamp         INT UNSIGNED
                                                                                                                                                                      UNIQUE KEY (collection, identifier)
                                                                                                                                                                    </code></pre>

                                                                                                                                                                    <h4>Change Detection</h4>
                                                                                                                                                                    <p>Re-embedding on every save would be expensive. Instead, <code>VectorService::embedAndStore()</code> computes an <b>MD5 hash</b> of the normalised text and compares it to the stored <code>content_hash</code>. If the hash matches, the HTTP call to the embedding server is skipped entirely.</p>

                                                                                                                                                                    <h4>Text Preparation</h4>
                                                                                                                                                                    <p>Before embedding, <code>DocumentEmbeddingAdapter::buildText()</code> strips all HTML tags from the <code>markup</code> field, collapses whitespace, and prepends the headline separated by a double newline: <code>"Headline

Body text…"</code>. This normalised plain text is what gets hashed and embedded.</p>

                                                                                                                                                                    <h4>Similarity Search</h4>
                                                                                                                                                                    <p><code>VectorService::findSimilar()</code> loads <em>all</em> vectors for a collection into memory, embeds the search query, then computes <b>cosine similarity</b> for each pair. Results are sorted descending by score and sliced to <code>topK</code>. This in-process approach works well for knowledge bases with hundreds to low thousands of documents.</p>

                                                                                                                                                                    <h4>Cosine Similarity Formula</h4>
                                                                                                                                                                    <p><code>similarity = (A · B) / (‖A‖ × ‖B‖)</code>. A value of <code>1.0</code> means identical direction (highly relevant); <code>0.0</code> means orthogonal (unrelated). Vectors with a zero norm are skipped with a warning. The default threshold of <code>0.30</code> is configurable via the Install Tool.</p>

                                                                                                                                                                    <h4>LlamaCppEmbeddingClient Retry Logic</h4>
                                                                                                                                                                    <p>The client has a built-in 4-attempt retry loop for HTTP 400 responses (which indicate a context-length overrun). On each failure the text is halved before retrying. A non-200 final response throws a <code>RuntimeException</code>.</p>', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (55, 1, 1776500000, 1776500000, 0, 0, 'CLI Commands & Scheduler Integration', '<h3>Re-Indexing Embeddings from the Command Line</h3>
                                                                                                                                                                    <p>The extension ships a Symfony Console command that can be run manually or scheduled via the TYPO3 Scheduler:</p>
                                                                                                                                                                    <pre><code>vendor/bin/typo3 knowledge-base:reindexDocumentEmbeddings</code></pre>

                                                                                                                                                                    <h4>What It Does</h4>
                                                                                                                                                                    <ol>
                                                                                                                                                                      <li>Checks whether the embedding server is reachable via <code>ModelAvailabilityService</code>. If not, exits with a failure code immediately.</li>
                                                                                                                                                                      <li>Fetches <b>all</b> documents from the repository (no visibility or storage-page filter).</li>
                                                                                                                                                                      <li>Calls <code>EmbeddingService::generateAndStoreIfChanged()</code> for each document. Thanks to hash-based change detection, only documents whose content has actually changed since the last run trigger a new HTTP call.</li>
                                                                                                                                                                      <li>Outputs the total count of processed documents.</li>
                                                                                                                                                                    </ol>

                                                                                                                                                                    <h4>When to Run It</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><b>After bulk imports</b> — when many documents are inserted directly into the database without going through the UI (which triggers embedding automatically).</li>
                                                                                                                                                                      <li><b>After switching the embedding model</b> — old vectors are incompatible with a new model; a full re-index is required.</li>
                                                                                                                                                                      <li><b>Scheduled nightly</b> — the command is marked <code>schedulable: true</code> in <code>Services.yaml</code> and can be configured in the TYPO3 Scheduler as a safety net.</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <p>The same functionality is also available as a one-off AJAX action (<code>reindexAction</code>) in the backend module for quick manual re-indexing without shell access.</p>', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (56, 1, 1776500000, 1776500000, 0, 0, 'AJAX Endpoints & JavaScript Integration', '<h3>Backend AJAX Routes</h3>
                                                                                                                                                                    <p>The module exposes three AJAX routes registered in <code>Configuration/Backend/AjaxRoutes.php</code>. All are called by the frontend ES module (<code>@vendor/typo3-incubator/knowledge-base/Backend</code>).</p>

                                                                                                                                                                    <table>
                                                                                                                                                                      <thead>
                                                                                                                                                                        <tr><th>Route Key</th><th>URL Path</th><th>Description</th></tr>
                                                                                                                                                                      </thead>
                                                                                                                                                                      <tbody>
                                                                                                                                                                        <tr>
                                                                                                                                                                          <td><code>loadDocument</code></td>
                                                                                                                                                                          <td><code>/knowledgebase/loadDocument?documentUid=N</code></td>
                                                                                                                                                                          <td>Returns the full JSON representation of a single document: headline, markup, breadcrumbs, type, visibility, reactions, comments.</td>
                                                                                                                                                                        </tr>
                                                                                                                                                                        <tr>
                                                                                                                                                                          <td><code>loadDocumentChildren</code></td>
                                                                                                                                                                          <td><code>/knowledgebase/loadDocumentChildren?documentUid=N</code></td>
                                                                                                                                                                          <td>Returns the direct children of a document for lazy-loading subtrees in the sidebar without a full page reload.</td>
                                                                                                                                                                        </tr>
                                                                                                                                                                        <tr>
                                                                                                                                                                          <td><code>searchDocuments</code></td>
                                                                                                                                                                          <td><code>/knowledgebase/searchDocuments?query=…&amp;mode=keyword|semantic|rag</code></td>
                                                                                                                                                                          <td>Runs the requested search mode and returns a unified JSON payload.</td>
                                                                                                                                                                        </tr>
                                                                                                                                                                      </tbody>
                                                                                                                                                                    </table>

                                                                                                                                                                    <h4>Search Response Shape</h4>
                                                                                                                                                                    <pre><code>{
                                                                                                                                                                      "mode":    "rag",
                                                                                                                                                                      "query":   "how do I create a board?",
                                                                                                                                                                      "results": [
                                                                                                                                                                        { "uid": 40, "headline": "Working with Boards", "score": 0.72, "breadcrumb": "…" }
                                                                                                                                                                      ],
                                                                                                                                                                      "answer":  "To create a board, …"   // null for keyword / semantic modes
                                                                                                                                                                    }</code></pre>

                                                                                                                                                                    <h4>JavaScript Module</h4>
                                                                                                                                                                    <p>The ES module is declared in <code>Configuration/JavaScriptModules.php</code> and maps to <code>EXT:knowledge-base/Resources/Public/JavaScript/</code>. It is loaded on every backend module page via <code>initializeAction()</code>. Responsibilities include:</p>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li>Tree node clicks — AJAX document load with no full-page reload</li>
                                                                                                                                                                      <li>Search input and mode switcher (keyword / semantic / RAG)</li>
                                                                                                                                                                      <li>CKEditor 5 rich-text editor (custom preset in <code>Configuration/Extensions/RteCkEditor/default.yaml</code>)</li>
                                                                                                                                                                      <li>Board drag-and-drop for tasks between columns and column reordering</li>
                                                                                                                                                                    </ul>', 'normal', 'public', 45, '1', 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (57, 0, 1776500000, 1776500000, 0, 0, 'Search Fallback & Graceful Degradation', '<h3>What Happens When AI is Unavailable?</h3>
                                                                                                                                                                    <p>The system is designed to keep working even when the local AI servers are offline. The <code>SearchService</code> implements a multi-level fallback chain so that search always returns something useful.</p>

                                                                                                                                                                    <h4>Fallback Chain</h4>
                                                                                                                                                                    <ol>
                                                                                                                                                                      <li><b>RAG mode requested</b>, but the <b>embedding server is down</b> → falls back to <b>keyword search</b>.</li>
                                                                                                                                                                      <li><b>RAG mode requested</b>, embedding server is up, but the <b>generation server is down</b> → falls back to <b>semantic search</b> (returns ranked documents without a generated answer).</li>
                                                                                                                                                                      <li><b>Semantic mode requested</b>, but the <b>embedding server is down</b> → falls back to <b>keyword search</b>.</li>
                                                                                                                                                                      <li><b>RAG mode</b>, both servers are up, but <b>no documents score above the similarity threshold</b> — the retrieval step falls back to a <b>fulltext search</b> for context documents, and the LLM still generates an answer from those results.</li>
                                                                                                                                                                    </ol>

                                                                                                                                                                    <h4>Health Checks</h4>
                                                                                                                                                                    <p>Before each search request, <code>ModelAvailabilityService</code> pings <code>GET /health</code> on each configured server with a 2-second timeout. Results are cached for the duration of the current PHP request (checked at most once per page load). Any exception — connection refused, timeout, or a non-2xx status — is treated as "unavailable" and logged at debug level.</p>

                                                                                                                                                                    <h4>Practical Implications</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li>You can start and stop the llama-server processes independently at any time — the UI automatically gains or loses AI features without any configuration change.</li>
                                                                                                                                                                      <li>Keyword search is always available as the last resort, relying only on MySQL FULLTEXT indexing with no external dependencies.</li>
                                                                                                                                                                    </ul>', 'normal', 'public', 42, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (58, 0, 1776438724, 1776500000, 1, 0, 'Comments & Reactions', '<h3>Team Collaboration on Documents</h3>
                                                                                                                                                                    <p>Every document — whether a normal article, a board, or a task card — supports two forms of lightweight team interaction.</p>

                                                                                                                                                                    <h4>Comments</h4>
                                                                                                                                                                    <p>Comments allow you to leave written feedback directly on a document:</p>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li>Expand the <b>Comments</b> section at the bottom of any open document.</li>
                                                                                                                                                                      <li>Type your message and submit. Comments are linked to your backend user account and stored with a timestamp.</li>
                                                                                                                                                                      <li>All team members who can see the document can also read and add comments.</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <h4>Reactions</h4>
                                                                                                                                                                    <p>Reactions are quick, emoji-style acknowledgements. Four types are available:</p>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><b>Like</b> 👍 — general approval</li>
                                                                                                                                                                      <li><b>Heart</b> ❤️ — appreciation</li>
                                                                                                                                                                      <li><b>Thumbs Down</b> 👎 — disagreement or concern</li>
                                                                                                                                                                      <li><b>Celebrate</b> 🎉 — milestone or achievement</li>
                                                                                                                                                                    </ul>
                                                                                                                                                                    <p>Each backend user can place one reaction per type per document. Reaction counts are shown aggregated alongside the document.</p>

                                                                                                                                                                    <h4>Privacy Note</h4>
                                                                                                                                                                    <p>Comments and reactions are stored as separate records linked to the document. If a document is set to <b>Private</b>, its comments and reactions are only visible to users who already have access to that document.</p>', 'normal', 'public', 39, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (59, 0, 1776500000, 1776500000, 0, 0, 'How Keyword Search Works', '<h3>MySQL FULLTEXT Search</h3>
                                                                                                                                                                    <p>Keyword search uses MySQL\'s built-in <b>FULLTEXT</b> indexing in <b>BOOLEAN MODE</b>. The index covers both the <code>headline</code> and <code>markup</code> columns of the documents table and is defined in <code>ext_tables.sql</code>:</p>
                                                                                                                                                                    <pre><code>FULLTEXT INDEX idx_search (headline, markup)</code></pre>

                                                                                                                                                                    <h4>Query Sanitisation</h4>
                                                                                                                                                                    <p>Before the query reaches MySQL, <code>DocumentRepository::search()</code> strips all FULLTEXT operator characters (<code>+ - &gt; &lt; ( ) ~ * " @</code>) from the input. Each remaining word is then rebuilt as a <b>prefix match</b> term (<code>+word*</code>), meaning:</p>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li><code>+</code> — all terms must be present (AND logic)</li>
                                                                                                                                                                      <li><code>*</code> — wildcard suffix, so "creat" also matches "create", "creating", "creation"</li>
                                                                                                                                                                    </ul>

                                                                                                                                                                    <h4>Ranking</h4>
                                                                                                                                                                    <p>Results are ordered by MySQL\'s <code>MATCH(headline, markup) AGAINST(…)</code> relevance score (<code>search_score DESC</code>). Documents where the terms appear in the headline typically rank higher than body-only matches.</p>

                                                                                                                                                                    <h4>Result Shape</h4>
                                                                                                                                                                    <p>Each result is returned as <code>[uid, headline, type, visibility, breadcrumb]</code> with <code>score: null</code> (scores are only present for semantic results).</p>

                                                                                                                                                                    <h4>Limitations</h4>
                                                                                                                                                                    <ul>
                                                                                                                                                                      <li>Matches only exact words or prefixes — no synonym or concept understanding.</li>
                                                                                                                                                                      <li>InnoDB FULLTEXT has a default minimum word length of 3 characters; very short terms may not be indexed.</li>
                                                                                                                                                                      <li>For concept-level discovery use <b>Semantic Search</b>; for direct questions use <b>Ask AI (RAG)</b>.</li>
                                                                                                                                                                    </ul>', 'normal', 'public', 42, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (60, 1, 1776500000, 1776500000, 0, 0, 'Smart Search Rollout', '<p>Tracks all tasks for setting up, tuning, and documenting the AI-powered search features of the knowledge base.</p>', 'board', 'public', 0, null, 0);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (61, 1, 1776500000, 1776500000, 0, 0, 'Tune semanticThreshold for better precision', '<p>adjust <b>semanticThreshold</b> in the Install Tool</p>', 'normal', 'public', 60, null, 10);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (62, 1, 1776500000, 1776500000, 0, 0, 'Schedule nightly re-index via TYPO3 Scheduler', '<p>Register the <code>knowledge-base:reindexDocumentEmbeddings</code> CLI command</p>', 'normal', 'public', 60, null, 10);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (63, 1, 1776500000, 1776500000, 0, 0, 'Evaluate embedding model for multilingual content', '<p>Compare similarity scores on a mixed-language test set before switching.</p>', 'normal', 'public', 60, null, 10);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (64, 1, 1776500000, 1776500000, 0, 0, 'Migrate legacy plain-text docs to rich markup', '<p>Convert documents to proper headings, lists, and paragraphs </p>', 'normal', 'public', 60, null, 11);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (65, 1, 1776500000, 1776500000, 0, 0, 'Set up llama-server for text generation', '<p>Start a second <code>llama-server</code> instance on port 8081</p>', 'normal', 'public', 60, null, 11);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (66, 1, 1776500000, 1776500000, 0, 0, 'Install smart-search package and configure endpoints', '<p>Installed the <code>smart-search</code> Composer package, ran database schema updates, and set <b>embeddingServerUrl</b> and <b>generationServerUrl</b> in the Install Tool Extension Configuration.</p>', 'normal', 'public', 60, null, 12);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (67, 1, 1776500000, 1776500000, 0, 0, 'Run initial full re-index of all documents', '<p>Ran <code>vendor/bin/typo3 knowledge-base:reindexDocumentEmbeddings</code> to generate embeddings for all existing documents. Verified vector count in <code>tx_smartsearch_vector</code> matches document count.</p>', 'normal', 'public', 60, null, 12);
INSERT INTO db.tx_knowledgebase_domain_model_document (uid, pid, tstamp, crdate, deleted, hidden, headline, markup, type, visibility, parent, user, status) VALUES (68, 1, 1776500000, 1776500000, 0, 0, 'Write team documentation for RAG and semantic search', '<p>Added documentation pages "How Semantic Search Works", "Asking the AI (RAG)", and "Search Fallback & Graceful Degradation" to the knowledge base. Team members can now self-serve answers about how the AI features work.</p>', 'normal', 'public', 60, null, 12);
