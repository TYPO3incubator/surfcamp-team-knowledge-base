<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\SmartSearch\Configuration\SmartSearchConfiguration;
use TYPO3Incubator\SmartSearch\Service\GenerationService;

class RagService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly DocumentRepository $documentRepository,
        private readonly GenerationService $generationService,
        private readonly DocumentService $documentService,
        private readonly SmartSearchConfiguration $configuration,
    ) {}

    /**
     * Semantic-only search: returns ranked documents with similarity scores, no LLM call.
     *
     * @return array<array{uid: int, headline: string, type: string, visibility: string, breadcrumb: array, score: float}>
     */
    public function searchSemantic(string $query, int $topKDocuments = 10): array
    {
        $hits = $this->embeddingService->findSimilar($query, $topKDocuments);

        if (empty($hits)) {
            return [];
        }

        $scoreByUid = array_column($hits, 'score', 'document_uid');
        $uids = array_column($hits, 'document_uid');
        $documents = $this->documentRepository->findByUids($uids);

        return array_map(fn(Document $d) => [
            'uid'        => $d->getUid(),
            'headline'   => $d->getHeadline(),
            'type'       => $d->getType(),
            'visibility' => $d->getVisibility(),
            'breadcrumb' => $d->getBreadcrumbs(),
            'score'      => $scoreByUid[$d->getUid()] ?? null,
        ], $documents);
    }

    /**
     * @return array{
     *     answer: string,
     *     sources: array<array{uid: int, headline: string, type: string, visibility: string, breadcrumb: array, score: float|null}>,
     *     mode: string
     * }
     */
    public function ask(string $query, int $topKDocuments = 10, float $semanticThreshold = 0.3): array
    {
        $semanticHits = $this->embeddingService->findSimilar($query, $topKDocuments);
        $aboveThreshold = array_filter(
            $semanticHits,
            fn(array $hit) => $hit['score'] >= $semanticThreshold
        );

        if (!empty($aboveThreshold)) {
            $scoreByUid = array_column($aboveThreshold, 'score', 'document_uid');
            $uids = array_column($aboveThreshold, 'document_uid');
            $documents = $this->documentRepository->findByUids($uids);
            $mode = 'semantic';
        } else {
            // Fallback to FULLTEXT search
            $scoreByUid = [];
            $results = $this->documentService->searchDocuments($query);
            $uids = array_column($results, 'uid');
            $documents = $this->documentRepository->findByUids($uids);
            $mode = 'fulltext';
        }

        if (empty($documents)) {
            return [
                'answer'  => 'No relevant documents found for your query.',
                'sources' => [],
                'mode'    => $mode,
            ];
        }

        $answer = $this->generationService->generate($query, $this->buildContextBlocks($documents));

        return [
            'answer'  => $answer,
            'sources' => array_map(fn(Document $d) => [
                'uid'        => $d->getUid(),
                'headline'   => $d->getHeadline(),
                'type'       => $d->getType(),
                'visibility' => $d->getVisibility(),
                'breadcrumb' => $d->getBreadcrumbs(),
                'score'      => $scoreByUid[$d->getUid()] ?? null,
            ], $documents),
            'mode'    => $mode,
        ];
    }

    /**
     * @param Document[] $documents
     * @return string[]
     */
    private function buildContextBlocks(array $documents): array
    {
        $blocks = [];
        foreach ($documents as $document) {
            $plainText = strip_tags($document->getMarkup());
            $plainText = (string)preg_replace('/\s+/', ' ', trim($plainText));
            $truncated = mb_substr($plainText, 0, $this->configuration->getDocumentContextLength());

            $blocks[] = sprintf(
                '[%d] Title: "%s"%s',
                $document->getUid(),
                $document->getHeadline(),
                $truncated !== '' ? "\n    Content: " . $truncated : ''
            );
        }
        return $blocks;
    }
}
