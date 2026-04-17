<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Status;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\StatusRepository;
use TYPO3Incubator\KnowledgeBase\Service\StatusService;

final class StatusServiceTest extends TestCase
{
    private DocumentRepository&MockObject $documentRepository;
    private PersistenceManager&MockObject $persistenceManager;
    private StatusRepository&MockObject $statusRepository;
    private StatusService $service;

    protected function setUp(): void
    {
        $this->documentRepository = $this->createMock(DocumentRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->statusRepository = $this->createMock(StatusRepository::class);

        $this->service = new StatusService(
            $this->documentRepository,
            $this->persistenceManager,
            $this->statusRepository,
        );
    }

    #[Test]
    public function createStatusAddsStatusWithCorrectOrdering(): void
    {
        $document = $this->makeDocument(1);
        $this->documentRepository->method('findByUid')->with(1)->willReturn($document);

        $existingStatuses = [$this->makeStatus(10, 'First', $document, 0)];
        $this->statusRepository->method('findByDocumentId')->with(1)->willReturn($existingStatuses);

        $this->statusRepository
            ->expects(self::once())
            ->method('add')
            ->with(self::callback(function (Status $s) use ($document): bool {
                return $s->getTitle() === 'Todo'
                    && $s->getDocument() === $document
                    && $s->getOrdering() === 1; // appended after the one existing status
            }));

        $this->persistenceManager->expects(self::once())->method('persistAll');

        $result = $this->service->createStatus(1, 'Todo');

        self::assertTrue($result['success']);
    }

    #[Test]
    public function createStatusAssignsOrderingZeroWhenNoExistingStatuses(): void
    {
        $document = $this->makeDocument(1);
        $this->documentRepository->method('findByUid')->willReturn($document);
        $this->statusRepository->method('findByDocumentId')->willReturn([]);

        $this->statusRepository
            ->expects(self::once())
            ->method('add')
            ->with(self::callback(fn(Status $s): bool => $s->getOrdering() === 0));

        $this->service->createStatus(1, 'Backlog');
    }

    #[Test]
    public function updateStatusReturnsErrorWhenStatusNotFound(): void
    {
        $this->statusRepository->method('findByUid')->willReturn(null);

        $result = $this->service->updateStatus(999, 'New title', 0);

        self::assertFalse($result['success']);
        self::assertNotEmpty($result['message']);
    }

    #[Test]
    public function updateStatusReordersAllStatusesCorrectly(): void
    {
        $document = $this->makeDocument(1);

        $statusA = $this->makeStatus(1, 'A', $document, 0);
        $statusB = $this->makeStatus(2, 'B', $document, 1);
        $statusC = $this->makeStatus(3, 'C', $document, 2);

        $this->statusRepository->method('findByUid')->with(1)->willReturn($statusA);
        // Return all three statuses when querying by document
        $this->statusRepository->method('findByDocumentId')->with(1)->willReturn([$statusA, $statusB, $statusC]);

        // Move statusA (uid=1) to position 2 (end)
        $this->statusRepository->expects(self::exactly(3))->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $result = $this->service->updateStatus(1, 'A', 2);

        self::assertTrue($result['success']);
        // After moving A to the end: B=0, C=1, A=2
        self::assertSame(0, $statusB->getOrdering());
        self::assertSame(1, $statusC->getOrdering());
        self::assertSame(2, $statusA->getOrdering());
    }

    #[Test]
    public function updateStatusClampsOrderingToBounds(): void
    {
        $document = $this->makeDocument(1);
        $statusA = $this->makeStatus(1, 'A', $document, 0);
        $statusB = $this->makeStatus(2, 'B', $document, 1);

        $this->statusRepository->method('findByUid')->willReturn($statusA);
        $this->statusRepository->method('findByDocumentId')->willReturn([$statusA, $statusB]);

        // Requesting ordering=99 should be clamped to 1 (count of others)
        $result = $this->service->updateStatus(1, 'A', 99);

        self::assertTrue($result['success']);
        self::assertSame(1, $statusA->getOrdering());
    }

    // --- helpers ---

    private function makeDocument(int $uid): Document
    {
        $document = new Document();
        $ref = new \ReflectionProperty($document, 'uid');
        $ref->setAccessible(true);
        $ref->setValue($document, $uid);
        return $document;
    }

    private function makeStatus(int $uid, string $title, Document $document, int $ordering): Status
    {
        $status = new Status();
        $ref = new \ReflectionProperty($status, 'uid');
        $ref->setAccessible(true);
        $ref->setValue($status, $uid);
        $status->setTitle($title);
        $status->setDocument($document);
        $status->setOrdering($ordering);
        return $status;
    }
}
