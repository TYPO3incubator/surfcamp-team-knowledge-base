<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Embedding;

use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\SmartSearch\Service\VectorService;

class DocumentEmbeddingAdapter
{
    private const COLLECTION = 'knowledge-base-documents';

    public function __construct(
        private readonly VectorService $vectorService,
    ) {}

    public function embedAndStoreIfChanged(Document $document): void
    {
        $text = $this->buildText($document);
        $this->vectorService->embedAndStore(self::COLLECTION, $document->getUid(), $text);
    }

    /**
     * @return array<array{identifier: string, score: float}>
     */
    public function findSimilar(string $query, int $topK): array
    {
        return $this->vectorService->findSimilar(self::COLLECTION, $query, $topK);
    }

    private function buildText(Document $document): string
    {
        $plain = strip_tags($document->getMarkup());
        $plain = (string)preg_replace('/\s+/', ' ', trim($plain));
        return $document->getHeadline() . "\n\n" . $plain;
    }
}
