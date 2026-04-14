<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Functional\Repository;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\EmbeddingRepository;
use TYPO3Incubator\KnowledgeBase\Tests\Functional\AbstractFunctionalTestBasis;

final class EmbeddingRepositoryTest extends AbstractFunctionalTestBasis
{
    protected array $testExtensionsToLoad = ['typo3-incubator/knowledge-base'];

    private EmbeddingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/EmbeddingFixtures.csv');
        $this->repository = $this->get(EmbeddingRepository::class);
    }

    #[Test]
    public function findContentHashByDocumentUidReturnsStoredHash(): void
    {
        $hash = $this->repository->findContentHashByDocumentUid(10);

        self::assertSame('abc123', $hash);
    }

    #[Test]
    public function findContentHashByDocumentUidReturnsNullForUnknownDocument(): void
    {
        $hash = $this->repository->findContentHashByDocumentUid(999);

        self::assertNull($hash);
    }

    #[Test]
    public function upsertCreatesNewRecord(): void
    {
        $this->repository->upsert(20, [0.1, 0.9], 'newhash');

        $hash = $this->repository->findContentHashByDocumentUid(20);
        self::assertSame('newhash', $hash);
    }

    #[Test]
    public function upsertUpdatesExistingRecord(): void
    {
        $this->repository->upsert(10, [0.3, 0.7], 'updatedhash');

        $hash = $this->repository->findContentHashByDocumentUid(10);
        self::assertSame('updatedhash', $hash);
    }

    #[Test]
    public function findAllReturnsNonDeletedNonHiddenDocuments(): void
    {
        $results = $this->repository->findAll();

        $uids = array_column($results, 'document_uid');
        self::assertContains(10, $uids);
        self::assertContains(11, $uids);
    }

    #[Test]
    public function findAllExcludesDeletedDocuments(): void
    {
        $results = $this->repository->findAll();

        $uids = array_column($results, 'document_uid');
        self::assertNotContains(12, $uids);
    }

    #[Test]
    public function findAllExcludesHiddenDocuments(): void
    {
        $results = $this->repository->findAll();

        $uids = array_column($results, 'document_uid');
        self::assertNotContains(13, $uids);
    }

    #[Test]
    public function findAllReturnsDecodedVectors(): void
    {
        $results = $this->repository->findAll();

        $byUid = [];
        foreach ($results as $r) {
            $byUid[$r['document_uid']] = $r['vector'];
        }

        self::assertSame([1.0, 0.0], $byUid[10]);
        self::assertSame([0.0, 1.0], $byUid[11]);
    }
}
