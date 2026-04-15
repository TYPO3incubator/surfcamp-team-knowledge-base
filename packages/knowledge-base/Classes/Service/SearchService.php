<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;

class SearchService
{
    public const string MODE_KEYWORD = 'keyword';
    public const string MODE_SEMANTIC = 'semantic';
    public const string MODE_RAG = 'rag';
    public const array VALID_MODES = [self::MODE_KEYWORD, self::MODE_SEMANTIC, self::MODE_RAG];

    public function __construct(
        protected readonly DocumentRepository $documentRepository,
        protected readonly BackendUserRepository $backendUserRepository,
        protected readonly Context $context,
        protected readonly EmbeddingService $embeddingService,
        protected readonly RagService $ragService,
        protected readonly DocumentService $documentService,
    ) {}

    public function searchDocuments(string $query): array
    {
        $documents = $this->documentRepository->search($query);

        return array_map(fn(Document $document) => [
            'uid' => $document->getUid(),
            'headline' => $document->getHeadline(),
            'type' => $document->getType(),
            'visibility' => $document->getVisibility(),
            'breadcrumb' => $document->getBreadcrumbs(),
        ], $documents);
    }

    public function buildKeywordResults(string $query): array
    {
        $documents = $this->documentService->searchDocuments($query);

        return [
            'mode'    => self::MODE_KEYWORD,
            'query'   => $query,
            'results' => array_map(
                fn(array $d) => $d + ['score' => null],
                $documents
            ),
            'answer'  => null,
        ];
    }

    public function buildSemanticResults(string $query): array
    {
        $results = $this->ragService->searchSemantic($query);

        return [
            'mode'    => self::MODE_SEMANTIC,
            'query'   => $query,
            'results' => $results,
            'answer'  => null,
        ];
    }

    public function buildRagResults(string $query): array
    {
        $ragResult = $this->ragService->ask($query);

        return [
            'mode'    => self::MODE_RAG,
            'query'   => $query,
            'results' => $ragResult['sources'],
            'answer'  => $ragResult['answer'],
        ];
    }
}
