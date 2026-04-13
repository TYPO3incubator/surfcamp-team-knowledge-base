<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;

class DocumentRepository
{
    private readonly string $tableName;

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly DataMapper $dataMapper
    ) {
        $this->tableName = $this->dataMapper->getDataMap(Document::class)->getTableName();
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function fetchNodesByParent(int $parentIdentifier): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentIdentifier, Connection::PARAM_INT))
            )
            ->orderBy('headline', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->dataMapper->map(Document::class, $rows);
    }

    public function hasChildren(int $uid): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        return (bool)$queryBuilder
            ->count('uid')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchOne();
    }
}
