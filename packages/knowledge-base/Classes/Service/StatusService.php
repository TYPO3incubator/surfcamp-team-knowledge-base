<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Status;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\StatusRepository;
use TYPO3Incubator\SmartSearch\Service\ModelAvailabilityService;

/**
 *
 */
class StatusService
{
    public function __construct(
        protected readonly DocumentRepository $documentRepository,
        protected readonly PersistenceManager $persistenceManager,
        protected StatusRepository $statusRepository,
    ) {}

    public function createStatus(int $documentUid, string $title): array
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

        $status = new Status();
        $status->setTitle($title);
        $status->setDocument($document);

        $existingStatuses = $this->statusRepository->findByDocumentId($documentUid);
        $status->setOrdering(count($existingStatuses));
        $this->statusRepository->add($status);
        $this->persistenceManager->persistAll();
        return $result;
    }

    public function updateStatus(int $statusId, string $title, int $ordering): array
    {
        $result = [
            'success' => true,
            'message' => '',
        ];
        $status = $this->statusRepository->findByUid($statusId);
        if ($status === null) {
            $result['success'] = false;
            $result['message'] = 'Status not found';
            return $result;
        }
        $status->setTitle($title);
        $status->setOrdering($ordering);
        $allStatuses = $this->statusRepository->findByDocumentId($status->getDocument()->getUid());
        // go over statuses and make sure the new ordering is correct and there are no duplicates
        // Remove the current status from the ordered list
        $otherStatuses = array_filter(
            $allStatuses,
            fn($s) => $s->getUid() !== $statusId
        );

        // Clamp the target ordering to valid bounds
        $ordering = max(0, min($ordering, count($otherStatuses)));
        $status->setOrdering($ordering);

        // Splice the updated status back in at the desired position and re-index
        array_splice($otherStatuses, $ordering, 0, [$status]);
        foreach ($otherStatuses as $index => $s) {
            $s->setOrdering($index);
            $this->statusRepository->update($s);
        }
        $this->persistenceManager->persistAll();
        return $result;

    }
}
