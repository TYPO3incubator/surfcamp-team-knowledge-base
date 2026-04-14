<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3Incubator\KnowledgeBase\Configuration\LlamaCppConfiguration;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Service\DocumentService;
use TYPO3Incubator\KnowledgeBase\Service\EmbeddingService;
use TYPO3Incubator\KnowledgeBase\Service\LlamaCppGenerationClient;
use TYPO3Incubator\KnowledgeBase\Service\RagService;

final class RagServiceTest extends TestCase
{
    private EmbeddingService&MockObject $embeddingService;
    private DocumentRepository&MockObject $documentRepository;
    private LlamaCppGenerationClient&MockObject $generationClient;
    private DocumentService&MockObject $documentService;
    private LlamaCppConfiguration&MockObject $configuration;
    private RagService $service;

    protected function setUp(): void
    {
        $this->embeddingService = $this->createMock(EmbeddingService::class);
        $this->documentRepository = $this->createMock(DocumentRepository::class);
        $this->generationClient = $this->createMock(LlamaCppGenerationClient::class);
        $this->documentService = $this->createMock(DocumentService::class);
        $this->configuration = $this->createMock(LlamaCppConfiguration::class);

        $this->configuration->method('getRagTopK')->willReturn(5);
        $this->configuration->method('getSemanticThreshold')->willReturn(0.30);
        $this->configuration->method('getDocumentContextLength')->willReturn(800);

        $this->service = new RagService(
            $this->embeddingService,
            $this->documentRepository,
            $this->generationClient,
            $this->documentService,
            $this->configuration,
        );
    }

    #[Test]
    public function askReturnsModeSemanticWhenResultsAboveThreshold(): void
    {
        $document = $this->makeDocument(1, 'Config Guide', '<p>Details</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 1, 'score' => 0.85]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $this->generationClient->method('complete')->willReturn('Here is the answer.');

        $result = $this->service->ask('How do I configure this?');

        self::assertSame('semantic', $result['mode']);
    }

    #[Test]
    public function askFallsBackToFulltextWhenNoSemanticResultsAboveThreshold(): void
    {
        $document = $this->makeDocument(1, 'Config Guide', '<p>Details</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 1, 'score' => 0.10]]);

        $this->documentService
            ->method('searchDocuments')
            ->willReturn([['uid' => 1, 'headline' => 'Config Guide', 'type' => 'normal', 'visibility' => 'public', 'breadcrumb' => []]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $this->generationClient->method('complete')->willReturn('Here is the answer.');

        $result = $this->service->ask('configure');

        self::assertSame('fulltext', $result['mode']);
    }

    #[Test]
    public function askReturnsAnswerAndSourcesShape(): void
    {
        $document = $this->makeDocument(42, 'My Doc', '<p>Content</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 42, 'score' => 0.90]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $this->generationClient->method('complete')->willReturn('Generated answer.');

        $result = $this->service->ask('What is My Doc?');

        self::assertArrayHasKey('answer', $result);
        self::assertArrayHasKey('sources', $result);
        self::assertArrayHasKey('mode', $result);
        self::assertSame('Generated answer.', $result['answer']);
        self::assertSame(42, $result['sources'][0]['uid']);
        self::assertSame('My Doc', $result['sources'][0]['headline']);
    }

    #[Test]
    public function askReturnsNotFoundMessageWhenNoDocumentsHydrated(): void
    {
        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 1, 'score' => 0.95]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([]);

        $result = $this->service->ask('test query');

        self::assertStringContainsString('No relevant documents', $result['answer']);
        self::assertSame([], $result['sources']);
    }

    #[Test]
    public function askPassesDocumentContextToGenerationClient(): void
    {
        $document = $this->makeDocument(1, 'TYPO3 Caching', '<p>Use caches wisely.</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 1, 'score' => 0.80]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $this->generationClient
            ->expects(self::once())
            ->method('complete')
            ->with(self::callback(function (array $messages): bool {
                $userContent = $messages[1]['content'] ?? '';
                return str_contains($userContent, 'TYPO3 Caching')
                    && str_contains($userContent, 'Use caches wisely.');
            }))
            ->willReturn('Answer.');

        $this->service->ask('caching question');
    }

    #[Test]
    public function askPassesConfiguredTopKToEmbeddingService(): void
    {
        $config = $this->createMock(LlamaCppConfiguration::class);
        $config->method('getRagTopK')->willReturn(3);
        $config->method('getSemanticThreshold')->willReturn(0.30);
        $config->method('getDocumentContextLength')->willReturn(800);

        $embeddingService = $this->createMock(EmbeddingService::class);
        $embeddingService
            ->expects(self::once())
            ->method('findSimilar')
            ->with('query', 3)
            ->willReturn([]);

        $documentService = $this->createMock(DocumentService::class);
        $documentService->method('searchDocuments')->willReturn([]);

        $documentRepository = $this->createMock(DocumentRepository::class);
        $documentRepository->method('findByUids')->willReturn([]);

        $service = new RagService(
            $embeddingService,
            $documentRepository,
            $this->generationClient,
            $documentService,
            $config,
        );

        $service->ask('query');
    }

    // --- searchSemantic tests ---

    #[Test]
    public function searchSemanticReturnsDocumentsWithScores(): void
    {
        $document = $this->makeDocument(7, 'Caching Guide', '<p>Cache all the things.</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 7, 'score' => 0.91]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $results = $this->service->searchSemantic('caching');

        self::assertCount(1, $results);
        self::assertSame(7, $results[0]['uid']);
        self::assertSame('Caching Guide', $results[0]['headline']);
        self::assertEqualsWithDelta(0.91, $results[0]['score'], 0.0001);
    }

    #[Test]
    public function searchSemanticReturnsExpectedResultShape(): void
    {
        $document = $this->makeDocument(3, 'Setup', '<p>Install first.</p>');

        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([['document_uid' => 3, 'score' => 0.75]]);

        $this->documentRepository
            ->method('findByUids')
            ->willReturn([$document]);

        $result = $this->service->searchSemantic('setup')[0];

        self::assertArrayHasKey('uid', $result);
        self::assertArrayHasKey('headline', $result);
        self::assertArrayHasKey('type', $result);
        self::assertArrayHasKey('visibility', $result);
        self::assertArrayHasKey('breadcrumb', $result);
        self::assertArrayHasKey('score', $result);
    }

    #[Test]
    public function searchSemanticReturnsEmptyArrayWhenNoEmbeddingsExist(): void
    {
        $this->embeddingService
            ->method('findSimilar')
            ->willReturn([]);

        $results = $this->service->searchSemantic('anything');

        self::assertSame([], $results);
    }

    #[Test]
    public function searchSemanticDoesNotCallGenerationClient(): void
    {
        $this->embeddingService->method('findSimilar')->willReturn([]);

        $this->generationClient->expects(self::never())->method('complete');

        $this->service->searchSemantic('query');
    }

    #[Test]
    public function searchSemanticPassesConfiguredTopKToEmbeddingService(): void
    {
        $this->embeddingService
            ->expects(self::once())
            ->method('findSimilar')
            ->with('query', 5)
            ->willReturn([]);

        $this->service->searchSemantic('query');
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
