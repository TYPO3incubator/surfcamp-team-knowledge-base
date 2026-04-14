# smart-search

Generic vector embedding, semantic search, and RAG (Retrieval-Augmented Generation) infrastructure for TYPO3 14.x. Provides the building blocks for any extension that wants to make its content semantically searchable and LLM-queryable, without being tied to any specific data structure.

## What it does

- **Vectorization** — embeds arbitrary text into float vectors and stores them in a generic table. Change detection via MD5 hashing avoids redundant API calls.
- **Semantic search** — finds the most relevant stored entries for a natural-language query using cosine similarity.
- **RAG generation** — given a query and pre-formatted context blocks, calls a chat LLM and returns a grounded answer.

The extension ships llama.cpp HTTP clients for both embedding and generation, and exposes interfaces (`EmbeddingClientInterface`, `GenerationClientInterface`) so you can swap in any other backend.

## Requirements

- TYPO3 14.x
- PHP 8.4+
- Two running [llama-server](https://github.com/ggml-org/llama.cpp) instances: one for embeddings, one for chat completion

## Server setup

### Embedding server (port 8080)

Uses [nomic-embed-text-v1.5](https://huggingface.co/nomic-ai/nomic-embed-text-v1.5-GGUF), a high-quality English embedding model. The `--ctx-size` and `--ubatch-size` must match the `embeddingContextLength` setting (2048 tokens ≈ ~8000 characters).

```bash
llama-server \
  -hf nomic-ai/nomic-embed-text-v1.5-GGUF \
  --port 8080 \
  --embeddings \
  --pooling mean \
  --ctx-size 2048 \
  --ubatch-size 2048
```

### Generation server (port 8081)

Uses [gemma-3-4b-it](https://huggingface.co/ggml-org/gemma-3-4b-it-GGUF), a compact instruction-tuned model that runs well on CPU.

```bash
llama-server \
  -hf ggml-org/gemma-3-4b-it-GGUF \
  --port 8081
```

Both commands download the model on first run via the `-hf` flag (requires `llama-server` built with `LLAMA_CURL=1`).

## Installation

Add the package to your project's `composer.json`:

```json
"typo3-incubator/smart-search": "dev-main"
```

Then run `composer update` and activate the extension:

```bash
vendor/bin/typo3 extension:activate smart-search
```

Run the database schema analyser (Install Tool → Maintenance → Analyze Database Structure) to create the `tx_smartsearch_vector` table.

## Configuration

All settings live under **Admin Tools → Settings → Extension Configuration → smart-search**.

| Key | Default | Description |
|-----|---------|-------------|
| `embeddingServerUrl` | `http://localhost:8080` | URL of the llama-server embedding instance |
| `generationServerUrl` | `http://localhost:8081` | URL of the llama-server chat instance |
| `generationMaxTokens` | `512` | Maximum tokens in a generated answer |
| `embeddingContextLength` | `6000` | Maximum characters passed to the embedding server (~4 chars/token; keep in sync with `--ctx-size`) |
| `ragTopK` | `5` | Default number of top documents retrieved for RAG context |
| `documentContextLength` | `800` | Maximum characters of content per document in the RAG context block |
| `semanticThreshold` | `0.30` | Minimum cosine similarity (0.0–1.0) to consider a result a semantic match |

## Usage

### Storing embeddings

Call `VectorService::embedAndStore()` whenever content changes. Pass a **collection** name (a string scoping your entries), a stable **identifier**, and the **plain text** to embed. HTML stripping is your responsibility before calling this method.

```php
use TYPO3Incubator\SmartSearch\Service\VectorService;

$vectorService->embedAndStore(
    collection: 'my-extension-articles',
    identifier: $article->getUid(),
    text: $article->getTitle() . "\n\n" . strip_tags($article->getBody())
);
```

The call is idempotent — if the text has not changed since the last call, the embedding server is not contacted.

### Semantic search

```php
$hits = $vectorService->findSimilar(
    collection: 'my-extension-articles',
    query: 'how do I configure caching?',
    topK: 5
);

// $hits = [['identifier' => '42', 'score' => 0.87], ...]
```

Results are sorted by cosine similarity descending. `identifier` is always a string; cast to `int` if your IDs are integers.

### RAG generation

```php
use TYPO3Incubator\SmartSearch\Service\GenerationService;

// Build context blocks however you like — one string per source document
$contextBlocks = array_map(
    fn($article) => sprintf('[%d] %s\n%s', $article->getUid(), $article->getTitle(), $excerpt),
    $relevantArticles
);

$answer = $generationService->generate(
    query: 'how do I configure caching?',
    contextBlocks: $contextBlocks
);
```

`GenerationService` assembles a system + user message and calls the chat LLM. The system prompt instructs the model to answer only from the provided documents and cite sources by their identifier.

### Removing vectors

```php
// Remove a single entry (e.g. when a record is deleted)
$vectorRepository->deleteByIdentifier('my-extension-articles', (string)$uid);

// Remove all entries for a collection (e.g. before a full reindex)
$vectorRepository->deleteByCollection('my-extension-articles');
```

## Database table

```sql
tx_smartsearch_vector (
    collection   VARCHAR(255)   -- scopes entries per extension/use-case
    identifier   VARCHAR(255)   -- stable ID within the collection
    vector       LONGTEXT       -- JSON-encoded float array
    content_hash VARCHAR(32)    -- MD5 for change detection
    tstamp       INT UNSIGNED
)
```

Multiple extensions can share the table without collision by using distinct collection names.

## Swapping the embedding or generation backend

Bind your own implementation in your extension's `Services.yaml`:

```yaml
TYPO3Incubator\SmartSearch\Embedding\EmbeddingClientInterface:
  alias: MyVendor\MyExtension\Embedding\OpenAiEmbeddingClient

TYPO3Incubator\SmartSearch\Generation\GenerationClientInterface:
  alias: MyVendor\MyExtension\Generation\OpenAiGenerationClient
```
