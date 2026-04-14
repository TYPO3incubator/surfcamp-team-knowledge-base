<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\EmbeddingRepository;
use TYPO3Incubator\KnowledgeBase\Embedding\EmbeddingClientInterface;
use TYPO3Incubator\KnowledgeBase\Service\EmbeddingService;

final class EmbeddingServiceTest extends TestCase
{
    private EmbeddingClientInterface&MockObject $embeddingClient;
    private EmbeddingRepository&MockObject $embeddingRepository;
    private EmbeddingService $service;

    protected function setUp(): void
    {
        $this->embeddingClient = $this->createMock(EmbeddingClientInterface::class);
        $this->embeddingRepository = $this->createMock(EmbeddingRepository::class);
        $this->service = new EmbeddingService($this->embeddingClient, $this->embeddingRepository);
    }

    #[Test]
    public function cosineSimilarityOfIdenticalVectorsIsOne(): void
    {
        $vector = [0.5, 0.5, 0.5, 0.5];

        $result = $this->service->cosineSimilarity($vector, $vector);

        self::assertEqualsWithDelta(1.0, $result, 0.0001);
    }

    #[Test]
    public function cosineSimilarityOfOrthogonalVectorsIsZero(): void
    {
        $a = [1.0, 0.0];
        $b = [0.0, 1.0];

        $result = $this->service->cosineSimilarity($a, $b);

        self::assertEqualsWithDelta(0.0, $result, 0.0001);
    }

    #[Test]
    public function cosineSimilarityOfZeroVectorIsZero(): void
    {
        $result = $this->service->cosineSimilarity([0.0, 0.0], [1.0, 1.0]);

        self::assertEqualsWithDelta(0.0, $result, 0.0001);
    }

    #[Test]
    public function generateAndStoreIfChangedSkipsEmbeddingWhenHashMatches(): void
    {
        $document = $this->makeDocument(1, 'Title', 'Content');
        $expectedHash = md5('Title' . "\n\n" . 'Content');

        $this->embeddingRepository
            ->method('findContentHashByDocumentUid')
            ->willReturn($expectedHash);

        $this->embeddingClient->expects(self::never())->method('embed');
        $this->embeddingRepository->expects(self::never())->method('upsert');

        $this->service->generateAndStoreIfChanged($document);
    }

    #[Test]
    public function generateAndStoreIfChangedCallsEmbedWhenHashDiffers(): void
    {
        $document = $this->makeDocument(1, 'Title', 'Content');

        $this->embeddingRepository
            ->method('findContentHashByDocumentUid')
            ->willReturn('old_hash');

        $this->embeddingClient
            ->expects(self::once())
            ->method('embed')
            ->willReturn([0.1, 0.2]);

        $this->embeddingRepository
            ->expects(self::once())
            ->method('upsert');

        $this->service->generateAndStoreIfChanged($document);
    }

    #[Test]
    public function generateAndStoreIfChangedStripsHtmlFromMarkup(): void
    {
        $document = $this->makeDocument(1, 'Title', '<p>Plain <b>text</b></p>');

        $this->embeddingRepository
            ->method('findContentHashByDocumentUid')
            ->willReturn(null);

        $this->embeddingClient
            ->expects(self::once())
            ->method('embed')
            ->with(self::stringContains('Plain text'))
            ->willReturn([0.1]);

        $this->service->generateAndStoreIfChanged($document);
    }

    #[Test]
    public function findSimilarReturnsTopKResultsSortedByScore(): void
    {
        $this->embeddingClient
            ->method('embed')
            ->willReturn([1.0, 0.0]);

        $this->embeddingRepository
            ->method('findAll')
            ->willReturn([
                ['document_uid' => 1, 'vector' => [1.0, 0.0]],  // score = 1.0
                ['document_uid' => 2, 'vector' => [0.0, 1.0]],  // score = 0.0
                ['document_uid' => 3, 'vector' => [0.7, 0.7]],  // score ~= 0.7
            ]);

        $results = $this->service->findSimilar('query', 2);

        self::assertCount(2, $results);
        self::assertSame(1, $results[0]['document_uid']);
        self::assertSame(3, $results[1]['document_uid']);
    }

    #[Test]
    public function findSimilarReturnsEmptyArrayWhenNoEmbeddingsExist(): void
    {
        $this->embeddingRepository->method('findAll')->willReturn([]);

        $results = $this->service->findSimilar('query');

        self::assertSame([], $results);
    }

    private function makeDocument(int $uid, string $headline, string $markup): Document
    {
        $document = new Document();
        // AbstractEntity stores uid as protected — set via reflection
        $ref = new \ReflectionProperty($document, 'uid');
        $ref->setAccessible(true);
        $ref->setValue($document, $uid);

        $document->setHeadline($headline);
        $document->setMarkup($markup);
        return $document;
    }
}
