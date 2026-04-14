<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3Incubator\KnowledgeBase\Configuration\LlamaCppConfiguration;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;

class RagService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly DocumentRepository $documentRepository,
        private readonly LlamaCppGenerationClient $generationClient,
        private readonly DocumentService $documentService,
        private readonly LlamaCppConfiguration $configuration,
    ) {}

    /**
     * Semantic-only search: returns ranked documents with similarity scores, no LLM call.
     *
     * @return array<array{uid: int, headline: string, type: string, visibility: string, breadcrumb: array, score: float}>
     */
    public function searchSemantic(string $query): array
    {
        $hits = $this->embeddingService->findSimilar($query, $this->configuration->getRagTopK());

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
    public function ask(string $query): array
    {
        $semanticHits = $this->embeddingService->findSimilar($query, $this->configuration->getRagTopK());
        $aboveThreshold = array_filter(
            $semanticHits,
            fn(array $hit) => $hit['score'] >= $this->configuration->getSemanticThreshold()
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

        $answer = $this->generate($query, $documents);

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
     */
    private function generate(string $query, array $documents): string
    {
        $contextBlocks = [];
        foreach ($documents as $index => $document) {
            $plainText = strip_tags($document->getMarkup());
            $plainText = preg_replace('/\s+/', ' ', trim($plainText));
            $truncated = mb_substr($plainText, 0, $this->configuration->getDocumentContextLength());

            $contextBlocks[] = sprintf(
                '[%d] Title: "%s"%s',
                $document->getUid(),
                $document->getHeadline(),
                $truncated !== '' ? "\n    Content: " . $truncated : ''
            );
        }

        $context = implode("\n\n", $contextBlocks);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant for a knowledge base. '
                    . 'Answer the question using only the provided documents. '
                    . 'Be detailed and cite your sources by their uid (e.g. [1], [2]).',
            ],
            [
                'role' => 'user',
                'content' => "Documents:\n\n{$context}\n\nQuestion: {$query}",
            ],
        ];

        return $this->generationClient->complete($messages);
    }
}
