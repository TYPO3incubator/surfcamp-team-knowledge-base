<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Functional\Service;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3Incubator\KnowledgeBase\Service\DocumentService;
use TYPO3Incubator\KnowledgeBase\Tests\Functional\AbstractFunctionalTestBasis;

final class DocumentServiceSearchTest extends AbstractFunctionalTestBasis
{
    protected array $testExtensionsToLoad = ['typo3-incubator/knowledge-base'];

    private DocumentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/DocumentSearchFixtures.csv');
        $this->service = $this->get(DocumentService::class);
    }

    private function requireMySQL(): void
    {
        $platform = $this->get(ConnectionPool::class)
            ->getConnectionForTable('tx_knowledgebase_domain_model_document')
            ->getDatabasePlatform();

        if (!($platform instanceof MySQLPlatform || $platform instanceof MariaDBPlatform)) {
            self::markTestSkipped('FULLTEXT search requires MySQL or MariaDB.');
        }
    }

    #[Test]
    public function searchDocumentsReturnsEmptyArrayForEmptyQuery(): void
    {
        $result = $this->service->searchDocuments('');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchDocumentsReturnsExpectedArrayShape(): void
    {
        $this->requireMySQL();

        $result = $this->service->searchDocuments('surfcamp');

        self::assertCount(1, $result);
        self::assertArrayHasKey('uid', $result[0]);
        self::assertArrayHasKey('headline', $result[0]);
        self::assertArrayHasKey('type', $result[0]);
        self::assertArrayHasKey('visibility', $result[0]);
    }

    #[Test]
    public function searchDocumentsReturnsCorrectValues(): void
    {
        $this->requireMySQL();

        $result = $this->service->searchDocuments('surfcamp');

        self::assertSame(2, $result[0]['uid']);
        self::assertSame('Welcome Document', $result[0]['headline']);
        self::assertSame('normal', $result[0]['type']);
        self::assertSame('public', $result[0]['visibility']);
    }

    #[Test]
    public function searchDocumentsDoesNotExposeMarkupInResult(): void
    {
        $this->requireMySQL();

        $result = $this->service->searchDocuments('surfcamp');

        self::assertArrayNotHasKey('markup', $result[0]);
    }

    #[Test]
    public function searchDocumentsExcludesDeletedRecords(): void
    {
        $this->requireMySQL();

        $result = $this->service->searchDocuments('deleted');

        self::assertSame([], $result);
    }
}
