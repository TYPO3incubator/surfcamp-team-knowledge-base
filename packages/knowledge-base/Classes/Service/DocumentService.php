<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\StatusRepository;
use TYPO3Incubator\SmartSearch\Service\ModelAvailabilityService;

class DocumentService
{
    public function __construct(
        protected readonly DocumentRepository $documentRepository,
        protected readonly BackendUserRepository $backendUserRepository,
        protected readonly Context $context,
        protected readonly EmbeddingService $embeddingService,
        private readonly ModelAvailabilityService $modelAvailabilityService,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly StatusRepository $statusRepository,
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
            $result['message'] = LocalizationUtility::translate('flash.document.notFound', 'Knowledge-base') ?? '';
            return $result;
        }

        $document->setHeadline($documentData['headline'] ?? $document->getHeadline());
        $document->setMarkup($documentData['markup'] ?? $document->getMarkup());
        $document->setVisibility($documentData['visibility'] ?? $document->getVisibility());
        if ($documentData['status']) {
            $status = $this->statusRepository->findByUid($documentData['status']);
            if ($status === null) {
                $result['success'] = false;
                $result['message'] = 'Status not found';
                return $result;
            }
            $document->setStatus($status);
        }

        $this->documentRepository->update($document);
        if ($this->modelAvailabilityService->isEmbeddingServerAvailable()) {
            $this->embeddingService->generateAndStoreIfChanged($document);
        }
        $this->persistenceManager->persistAll();
        return $result;
    }

    public function createDocument(string $documentHeadline, int $parentId, string $type, string $documentDescription): array
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
            $result['message'] = LocalizationUtility::translate('flash.document.userNotFound', 'Knowledge-base') ?? 'Backend user not found.';
            return $result;
        }


        $document = new Document();
        $document->setHeadline($documentHeadline);
        $document->setUser($backendUser);
        $document->setType($type);
		$document->setMarkup($documentDescription);

        if ($parentId > 0) {
            $parent = $this->documentRepository->findByUid($parentId);
            if ($parent instanceof Document) {
                $document->setParent($parent);
            }
        }

        $this->documentRepository->add($document);
        $this->persistenceManager->persistAll();
        $result['documentUid'] = $document->getUid();
        $result['parentType'] = $document->getParent()->getType();

		if ($this->modelAvailabilityService->isEmbeddingServerAvailable()) {
			$this->embeddingService->generateAndStoreIfChanged($document);
		}

        return $result;
    }
    public function loadDocument(int $documentUid): array
    {
        $result = [
            'success' => true,
            'message' => '',
            'document' => null,
            'commands' => [],
        ];

        $document = $this->documentRepository->findByUid($documentUid);
        if ($document === null) {
            $result['success'] = false;
            $result['message'] = LocalizationUtility::translate('flash.document.notFound', 'Knowledge-base') ?? 'Document not found.';
            return $result;
        }

        $result['document'] = $document;

        return $result;
    }

    public function loadDocumentChildren(int $documentUid): array
    {
        $children = $this->documentRepository->getChildren($documentUid);
        return $children;
    }

    public function deleteDocument(int $documentUid): array
    {
        $result = [
            'success' => true,
            'message' => '',
        ];
        $document = $this->documentRepository->findByUid($documentUid);
        if ($document === null) {
            $result['success'] = false;
            $result['message'] = LocalizationUtility::translate('flash.document.notFound', 'Knowledge-base') ?? '';
            return $result;
        }

        $this->deleteDocumentRecursive($document);
        $this->persistenceManager->persistAll();

        return $result;
    }

    protected function deleteDocumentRecursive(Document $document): void
    {
        $children = $this->documentRepository->getChildren((int)$document->getUid());
        foreach ($children as $child) {
            $this->deleteDocumentRecursive($child);
        }
        $this->documentRepository->remove($document);
    }
}
