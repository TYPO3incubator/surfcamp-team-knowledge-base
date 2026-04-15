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

## System requirements

### Option A — llama.sh (local binary)

| Requirement | Notes |
|-------------|-------|
| [llama.cpp](https://github.com/ggml-org/llama.cpp) | Must be built with `LLAMA_CURL=1` so `-hf` model downloads work. Install via `brew install llama.cpp` on macOS (Homebrew formula ships with curl support), or build from source. |
| `llama-server` on `$PATH` | Verify with `llama-server --version` |
| ~6 GB free disk space | Models are cached in `~/.cache/huggingface` after the first download |
| ~4 GB RAM (CPU inference) | The gemma-3-4b generation model requires roughly 4 GB; embedding model is much lighter |

### Option B — DDEV

| Requirement | Notes |
|-------------|-------|
| [DDEV](https://ddev.com) v1.23+ | |
| Docker with ≥6 GB memory allocated | Models live in named Docker volumes; disk usage is similar to Option A |
| No local `llama-server` needed | The Docker image `ghcr.io/ggml-org/llama.cpp:server` is used |

## Server setup

### Option A — llama.sh (recommended for local development)

The repository ships a `llama.sh` helper at the project root that manages both server processes, PID files, and log output.

**Start both servers:**

```bash
./llama.sh
# or explicitly: ./llama.sh start
```

On first run each server downloads its model from Hugging Face (~300 MB for the embedding model, ~2.5 GB for the generation model). Subsequent starts are instant.

**Check status:**

```bash
./llama.sh status
```

**Follow logs:**

```bash
tail -f var/log/llama-embed.log
tail -f var/log/llama-generate.log
```

**Stop both servers:**

```bash
./llama.sh stop
```

After starting, verify both servers are healthy:

```bash
curl -s http://localhost:8080/health   # embedding server
curl -s http://localhost:8081/health   # generation server
```

Both should return `{"status":"ok"}`. The extension is pre-configured to reach the servers at these URLs — no further configuration is needed for a default local setup.

### Option B — DDEV

The project ships `.ddev/docker-compose.llama.yaml` which defines the two llama services under the `llama` Docker Compose profile. Start them alongside the web container with:

```bash
ddev start --profile llama
```

Models are downloaded from Hugging Face on first start and cached in named Docker volumes (`llama-embed-models`, `llama-generate-models`), so subsequent starts skip the download.

Watch download progress on first run:

```bash
ddev logs -s llama-embed
ddev logs -s llama-generate
```

Verify the servers are up from inside the web container:

```bash
ddev exec curl -s http://llama-embed:8080/health
ddev exec curl -s http://llama-generate:8081/health
```

When using DDEV, update the server URLs in **Admin Tools → Settings → Extension Configuration → smart-search**:

| Setting | DDEV value |
|---------|------------|
| `embeddingServerUrl` | `http://llama-embed:8080` |
| `generationServerUrl` | `http://llama-generate:8081` |

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
