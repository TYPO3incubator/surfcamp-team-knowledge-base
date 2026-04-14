# Semantic Search & RAG Setup

This guide covers installing llama.cpp, downloading the required models, and running the inference servers that power semantic search and RAG in the knowledge base.

## Overview

Two servers need to run alongside TYPO3:

| Server | Port | Purpose | Model |
|---|---|---|---|
| Embedding server | 8080 | Converts text to vectors for semantic search | `nomic-embed-text-v1.5` |
| Generation server | 8081 | Generates answers from retrieved documents | `gemma-3-4b-it` |

Both ports and all other settings can be changed in the TYPO3 backend under **Admin Tools → Settings → Extension Configuration → knowledge-base**.

---

## 1. Install llama.cpp

Official repository: [https://github.com/ggml-org/llama.cpp](https://github.com/ggml-org/llama.cpp)

### macOS

```bash
brew install llama.cpp
```

Metal GPU acceleration is enabled automatically on Apple Silicon and Intel Macs with a supported GPU.

### Linux — pre-built binaries

Download the latest release binary from the [releases page](https://github.com/ggml-org/llama.cpp/releases). Binaries are provided for CPU-only and CUDA builds.

### Linux — build from source

**CPU only:**
```bash
sudo apt-get install -y build-essential cmake libcurl4-openssl-dev

git clone https://github.com/ggml-org/llama.cpp
cd llama.cpp
cmake -B build
cmake --build build --config Release -j$(nproc)
```

**NVIDIA GPU (CUDA):**
```bash
cmake -B build -DGGML_CUDA=ON
cmake --build build --config Release -j$(nproc)
```

**AMD GPU (HIP/ROCm):**
```bash
cmake -B build -DGGML_HIP=ON -DAMDGPU_TARGETS=gfx906
cmake --build build --config Release -j$(nproc)
```

**Cross-platform GPU (Vulkan):**
```bash
cmake -B build -DGGML_VULKAN=ON
cmake --build build --config Release -j$(nproc)
```

After building, the binary is at `build/bin/llama-server`. Optionally install it globally:
```bash
sudo cp build/bin/llama-server /usr/local/bin/
```

### Supported GPU backends

| Backend | Hardware |
|---|---|
| Metal | Apple Silicon and AMD GPUs on macOS (enabled by default) |
| CUDA | NVIDIA GPUs |
| HIP | AMD GPUs on Linux |
| Vulkan | General cross-platform GPU support |
| SYCL | Intel and NVIDIA GPUs |

---

## 2. Download the models

llama.cpp requires models in **GGUF format**. Models are available directly on Hugging Face.

### Using llama-cli (simplest — no separate download step)

llama.cpp can download a model from Hugging Face and start a server in one command:

```bash
# Start generation server directly (downloads model on first run)
llama-server -hf ggml-org/gemma-3-4b-it-GGUF --port 8081
llama-server -hf nomic-ai/nomic-embed-text-v1.5-GGUF --port 8081
```

The `-hf <user>/<model>[:quant]` flag fetches the model and caches it locally. Specify a quantization with a colon suffix, e.g. `-hf ggml-org/gemma-3-4b-it-GGUF:Q4_K_M`.

### Using huggingface-cli (recommended for explicit control)

```bash
pip install huggingface_hub

# Embedding model (~137 MB)
huggingface-cli download nomic-ai/nomic-embed-text-v1.5-GGUF \
  nomic-embed-text-v1.5.Q8_0.gguf \
  --local-dir ./models

# Generation model (~2.5 GB)
huggingface-cli download unsloth/gemma-3-4b-it-GGUF \
  gemma-3-4b-it-Q4_K_M.gguf \
  --local-dir ./models
```

### Using curl

```bash
mkdir -p models

# Embedding model
curl -L -o models/nomic-embed-text-v1.5.Q8_0.gguf \
  "https://huggingface.co/nomic-ai/nomic-embed-text-v1.5-GGUF/resolve/main/nomic-embed-text-v1.5.Q8_0.gguf"

# Generation model
curl -L -o models/gemma-3-4b-it-Q4_K_M.gguf \
  "https://huggingface.co/unsloth/gemma-3-4b-it-GGUF/resolve/main/gemma-3-4b-it-Q4_K_M.gguf"
```

### Quantization levels

llama.cpp supports 1.5-bit through 8-bit integer quantization. Lower bit-depth means smaller file and faster inference at the cost of quality. Common levels:

| Quantization | Size (4B model) | Notes |
|---|---|---|
| `Q2_K` | ~1.6 GB | Smallest, noticeable quality loss |
| `Q4_K_M` | ~2.5 GB | **Recommended default** — good balance |
| `Q5_K_M` | ~3.1 GB | Better quality, modest size increase |
| `Q8_0` | ~4.3 GB | Near-lossless, largest of the common formats |

For the embedding model (`nomic-embed-text-v1.5`), `Q8_0` is recommended — the model is small enough that the size difference is negligible and quality matters more.

### Alternative generation models

| Model file | Repo | Size | Notes |
|---|---|---|---|
| `gemma-3-4b-it-Q4_K_M.gguf` | `unsloth/gemma-3-4b-it-GGUF` | ~2.5 GB | Default recommendation |
| `gemma-3-4b-it-Q8_0.gguf` | `unsloth/gemma-3-4b-it-GGUF` | ~4.3 GB | Higher quality |
| `gemma-3-12b-it-Q4_K_M.gguf` | `unsloth/gemma-3-12b-it-GGUF` | ~7.6 GB | Better answers, needs 10+ GB RAM |

---

## 3. Start the servers

Run each server in its own terminal (or use the shell script in section 5).

### Embedding server (port 8080)

```bash
llama-server \
  --model models/nomic-embed-text-v1.5.Q8_0.gguf \
  --port 8080 \
  --embeddings \
  --pooling mean \
  --ctx-size 2048 \
  --ubatch-size 2048 \
  --log-disable
```

| Flag | Purpose |
|---|---|
| `--embeddings` | Enables the `/embedding` endpoint required for vector generation |
| `--pooling mean` | Mean pooling strategy required by the nomic model |
| `--ctx-size 2048` | Max input tokens — matches the model's training context length |
| `--ubatch-size 2048` | Physical batch size — must be ≥ the longest input you expect to embed |

Verify it is running:

```bash
curl -s http://localhost:8080/health
# expected: {"status":"ok"}
```

### Generation server (port 8081)

```bash
llama-server \
  --model models/gemma-3-4b-it-Q4_K_M.gguf \
  --port 8081 \
  --ctx-size 8192 \
  -ngl 99 \
  --log-disable
```

| Flag | Purpose |
|---|---|
| `--ctx-size 8192` | Context window — larger values allow more document context in the prompt |
| `-ngl 99` | Number of layers to offload to GPU. Set to `0` for CPU-only, `99` to offload everything |
| `--log-disable` | Suppresses per-request logs in the terminal |

Verify it is running:

```bash
curl -s http://localhost:8081/health
# expected: {"status":"ok"}
```

---

## 4. Index existing documents

Once both servers are running, trigger a one-time reindex to generate embeddings for all existing documents. This can be done from the TYPO3 backend by calling the reindex action.

> New and updated documents are indexed automatically on save going forward — the reindex is only needed for content that existed before semantic search was enabled, or after switching to a different embedding model.

---

## 5. Keeping the servers running (optional)

For a persistent dev setup, save this as `start-llm-servers.sh` in the project root:

```bash
#!/bin/bash
# start-llm-servers.sh

MODELS_DIR="$(dirname "$0")/models"

llama-server \
  --model "$MODELS_DIR/nomic-embed-text-v1.5.Q8_0.gguf" \
  --port 8080 --embeddings --pooling mean --ctx-size 2048 --ubatch-size 2048 --log-disable &

llama-server \
  --model "$MODELS_DIR/gemma-3-4b-it-Q4_K_M.gguf" \
  --port 8081 --ctx-size 8192 -ngl 99 --log-disable &

echo "Embedding server: http://localhost:8080"
echo "Generation server: http://localhost:8081"
wait
```

```bash
chmod +x start-llm-servers.sh
./start-llm-servers.sh
```

---

## 6. Configuration reference

All settings are available in the TYPO3 backend under **Admin Tools → Settings → Extension Configuration → knowledge-base**.

| Setting | Default | Description |
|---|---|---|
| `embeddingServerUrl` | `http://localhost:8080` | URL of the embedding llama-server |
| `generationServerUrl` | `http://localhost:8081` | URL of the generation llama-server |
| `generationMaxTokens` | `512` | Maximum tokens in the generated answer |
| `ragTopK` | `5` | Number of documents retrieved per query |
| `documentContextLength` | `800` | Max characters of each document included in the prompt |
| `semanticThreshold` | `0.30` | Minimum cosine similarity to use semantic results; below this falls back to keyword search |

---

## Troubleshooting

**`llama-server: command not found`**
On Linux after building from source, either add `build/bin/` to your `PATH` or copy the binary to `/usr/local/bin/`.

**Embedding server returns empty vectors**
Make sure `--embeddings` and `--pooling mean` are both passed. The nomic model requires mean pooling — omitting `--pooling mean` produces zero or garbage vectors.

**Slow generation on CPU**
Use a smaller quantization: replace `Q4_K_M` with `Q2_K` (~1.6 GB). Quality will be lower but speed improves significantly. Alternatively, set `-ngl` to a partial layer count to offload only part of the model to GPU if VRAM is limited.

**Out of VRAM**
Reduce `-ngl` from `99` to a lower value (e.g. `20`) to keep some layers on CPU. The model will still run; only the offloaded layers use GPU memory.

**Port already in use**
Change the ports in the server commands and update the URLs in the TYPO3 extension configuration accordingly.

**Model not in GGUF format**
llama.cpp only loads GGUF files. Use the conversion scripts in the llama.cpp repository (`convert_*.py`) or the [GGUF-my-repo](https://huggingface.co/spaces/ggml-org/gguf-my-repo) space on Hugging Face to convert from other formats.
