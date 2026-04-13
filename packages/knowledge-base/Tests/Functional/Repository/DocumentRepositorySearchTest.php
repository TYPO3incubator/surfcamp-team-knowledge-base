<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Functional\Repository;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Tests\Functional\AbstractFunctionalTestBasis;

final class DocumentRepositorySearchTest extends AbstractFunctionalTestBasis
{
    protected array $testExtensionsToLoad = ['typo3-incubator/knowledge-base'];

    private DocumentRepository $repository;

    protected function setUp(): void
    {
        if (!getenv('typo3DatabasePassword')) {
            putenv('typo3DatabasePassword=root');
            putenv('typo3DatabaseUsername=root');
            putenv('typo3DatabaseHost=db');
            putenv('typo3DatabaseName=functionalSeminars');
        }
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/DocumentSearchFixtures.csv');
        $this->repository = $this->get(DocumentRepository::class);
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
    public function searchReturnsEmptyArrayForEmptyQuery(): void
    {
        $result = $this->repository->search('');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchReturnsEmptyArrayForWhitespaceOnlyQuery(): void
    {
        $result = $this->repository->search('   ');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchReturnsEmptyArrayForOnlyBooleanOperators(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('+++--->>>');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchFindsDocumentByHeadline(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('introduction');

        self::assertCount(1, $result);
        self::assertInstanceOf(Document::class, $result[0]);
        self::assertSame('Introduction to TYPO3', $result[0]->getHeadline());
    }

    #[Test]
    public function searchFindsDocumentByMarkupContent(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('surfcamp');

        self::assertCount(1, $result);
        self::assertSame('Welcome Document', $result[0]->getHeadline());
    }

    #[Test]
    public function searchMatchesMultipleDocuments(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('knowledge');

        self::assertCount(3, $result);
    }

    #[Test]
    public function searchRanksDocumentWithMatchInBothFieldsFirst(): void
    {
        $this->requireMySQL();

        // uid=3 has "knowledge" in both headline AND markup — should rank above uid=4 (headline only)
        $result = $this->repository->search('knowledge');

        self::assertSame('Knowledge Base Overview', $result[0]->getHeadline());
    }

    #[Test]
    public function searchSupportsPrefixMatching(): void
    {
        $this->requireMySQL();

        // "organ" should match "organizing" via the wildcard suffix
        $result = $this->repository->search('organ');

        self::assertCount(1, $result);
        self::assertSame('Knowledge Article', $result[0]->getHeadline());
    }

    #[Test]
    public function searchStripsInjectedBooleanOperatorsFromQuery(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('+++introduction---');

        self::assertCount(1, $result);
        self::assertSame('Introduction to TYPO3', $result[0]->getHeadline());
    }

    #[Test]
    public function searchExcludesDeletedDocuments(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('deleted');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchExcludesHiddenDocuments(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('hidden');

        self::assertSame([], $result);
    }

    #[Test]
    public function searchReturnsDocumentObjects(): void
    {
        $this->requireMySQL();

        $result = $this->repository->search('surfcamp');

        self::assertContainsOnlyInstancesOf(Document::class, $result);
    }
}
