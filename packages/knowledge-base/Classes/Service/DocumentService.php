<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;

class DocumentService
{
    public function __construct(
        protected readonly DocumentRepository $documentRepository
    ) {}

    public function updateDocument(int $documentUid, array $documentData): array
    {
        $result = [
            'success' => true,
            'message' => '',
        ];
        $document = $this->documentRepository->findByUid($documentUid);
        if ($document === null) {
            $result['success'] = false;
            $result['message'] = LocalizationUtility::translate('flash.document.notFound', 'KnowledgeBase') ?? '';
            return $result;
        }

        $document->setHeadline($documentData['headline'] ?? $document->getHeadline());
        $document->setMarkup($documentData['markup'] ?? $document->getMarkup());
        $document->setVisibility($documentData['visibility'] ?? $document->getVisibility());

        $this->documentRepository->update($document);
        return $result;
    }
}
