<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Embedding\DocumentEmbeddingAdapter;

class EmbeddingService
{
    public function __construct(
        private readonly DocumentEmbeddingAdapter $documentEmbeddingAdapter,
    ) {}

    public function generateAndStoreIfChanged(Document $document): void
    {
        $this->documentEmbeddingAdapter->embedAndStoreIfChanged($document);
    }

    /**
     * Find the most semantically similar documents for the given query.
     *
     * @return array<array{document_uid: int, score: float}> Sorted by score descending
     */
    public function findSimilar(string $query, int $topK = 5): array
    {
        $hits = $this->documentEmbeddingAdapter->findSimilar($query, $topK);

        return array_map(static fn(array $h) => [
            'document_uid' => (int)$h['identifier'],
            'score' => $h['score'],
        ], $hits);
    }
}
