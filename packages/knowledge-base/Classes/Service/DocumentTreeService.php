<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;

class DocumentTreeService
{
    public function __construct(
        protected readonly DocumentRepository $documentRepository
    ) {}

    public function getFullTree(): array
    {
        return $this->getNodesRecursive(0);
    }

    protected function getNodesRecursive(int $parentIdentifier): array
    {
        $documents = $this->documentRepository->fetchSlimNodesByParent($parentIdentifier);

        $items = [];
        foreach ($documents as $document) {
            $items[] = [
                'document' => $document,
                'children' => $this->getNodesRecursive((int)$document->getUid()),
            ];
        }

        return $items;
    }
}
