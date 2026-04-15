<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Embedding\DocumentEmbeddingAdapter;
use TYPO3Incubator\KnowledgeBase\Service\EmbeddingService;

final class EmbeddingServiceTest extends TestCase
{
    private DocumentEmbeddingAdapter&MockObject $adapter;
    private EmbeddingService $service;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(DocumentEmbeddingAdapter::class);
        $this->service = new EmbeddingService($this->adapter);
    }

    #[Test]
    public function generateAndStoreIfChangedDelegatesToAdapter(): void
    {
        $document = $this->makeDocument(1, 'Title', 'Content');

        $this->adapter
            ->expects(self::once())
            ->method('embedAndStoreIfChanged')
            ->with($document);

        $this->service->generateAndStoreIfChanged($document);
    }

    #[Test]
    public function findSimilarMapsIdentifierToDocumentUid(): void
    {
        $this->adapter
            ->method('findSimilar')
            ->willReturn([
                ['identifier' => '42', 'score' => 0.9],
                ['identifier' => '7', 'score' => 0.5],
            ]);

        $results = $this->service->findSimilar('query', 5);

        self::assertCount(2, $results);
        self::assertSame(42, $results[0]['document_uid']);
        self::assertSame(0.9, $results[0]['score']);
        self::assertSame(7, $results[1]['document_uid']);
    }

    #[Test]
    public function findSimilarPassesQueryAndTopKToAdapter(): void
    {
        $this->adapter
            ->expects(self::once())
            ->method('findSimilar')
            ->with('test query', 3)
            ->willReturn([]);

        $this->service->findSimilar('test query', 3);
    }

    #[Test]
    public function findSimilarReturnsEmptyArrayWhenAdapterReturnsEmpty(): void
    {
        $this->adapter->method('findSimilar')->willReturn([]);

        self::assertSame([], $this->service->findSimilar('query'));
    }

    private function makeDocument(int $uid, string $headline, string $markup): Document
    {
        $document = new Document();
        $ref = new \ReflectionProperty($document, 'uid');
        $ref->setAccessible(true);
        $ref->setValue($document, $uid);
        $document->setHeadline($headline);
        $document->setMarkup($markup);
        return $document;
    }
}
