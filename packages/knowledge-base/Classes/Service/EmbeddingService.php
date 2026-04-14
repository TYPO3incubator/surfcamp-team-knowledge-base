<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3Incubator\KnowledgeBase\Configuration\LlamaCppConfiguration;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\EmbeddingRepository;
use TYPO3Incubator\KnowledgeBase\Embedding\EmbeddingClientInterface;

class EmbeddingService
{
    public function __construct(
        private readonly EmbeddingClientInterface $embeddingClient,
        private readonly EmbeddingRepository $embeddingRepository,
        private readonly LlamaCppConfiguration $configuration,
    ) {}

    public function generateAndStoreIfChanged(Document $document): void
    {
        $embedText = $this->buildEmbedText($document);
        $hash = md5($embedText);
        $storedHash = $this->embeddingRepository->findContentHashByDocumentUid($document->getUid());

        if ($storedHash === $hash) {
            return;
        }

        $vector = $this->embeddingClient->embed($embedText);
        $this->embeddingRepository->upsert($document->getUid(), $vector, $hash);
    }

    /**
     * Find the most semantically similar documents for the given query.
     *
     * @return array<array{document_uid: int, score: float}> Sorted by score descending
     */
    public function findSimilar(string $query, int $topK = 5): array
    {
        $all = $this->embeddingRepository->findAll();

        if (empty($all)) {
            return [];
        }

        $queryVector = $this->embeddingClient->embed($query);

        $scored = [];
        foreach ($all as $entry) {
            $score = $this->cosineSimilarity($queryVector, $entry['vector']);
            $scored[] = ['document_uid' => $entry['document_uid'], 'score' => $score];
        }

        usort($scored, static fn(array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $topK);
    }

    /**
     * @param float[] $a
     * @param float[] $b
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $length = min(count($a), count($b));
        for ($i = 0; $i < $length; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function buildEmbedText(Document $document): string
    {
        $plainText = strip_tags($document->getMarkup());
        $plainText = preg_replace('/\s+/', ' ', trim($plainText));

        $maxChars = $this->configuration->getEmbeddingContextLength();
        if (mb_strlen($plainText) > $maxChars) {
            $plainText = mb_substr($plainText, 0, $maxChars);
        }

        return $document->getHeadline() . "\n\n" . $plainText;
    }
}
