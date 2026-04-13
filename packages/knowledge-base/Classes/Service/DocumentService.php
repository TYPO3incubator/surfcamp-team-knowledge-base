<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;

class DocumentService
{
    public function __construct(
        protected readonly DocumentRepository $documentRepository,
        protected readonly BackendUserRepository $backendUserRepository,
        protected readonly Context $context,
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

    public function createDocument(string $documentHeadline, int $parentId): array
    {
        $result = [
            'success' => true,
            'message' => '',
            'documentUid' => 0,
        ];

        $backendUserUid = $this->context->getPropertyFromAspect('backend.user', 'id');
        $backendUser = $this->backendUserRepository->findByUid($backendUserUid);

        if ($backendUser === null) {
            $result['success'] = false;
            $result['message'] = LocalizationUtility::translate('flash.document.userNotFound', 'KnowledgeBase') ?? 'Backend user not found.';
            return $result;
        }

        $document = new Document();
        $document->setHeadline($documentHeadline);
        $document->setUser($backendUser);

        if ($parentId > 0) {
            $parent = $this->documentRepository->findByUid($parentId);
            if ($parent instanceof Document) {
                $document->setParent($parent);
            }
        }

        $this->documentRepository->add($document);
        $result['documentUid'] = $document->getUid();

        return $result;
    }
}
