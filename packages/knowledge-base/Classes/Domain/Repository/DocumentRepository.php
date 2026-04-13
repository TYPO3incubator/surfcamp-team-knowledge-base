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

    public function findByUid(int $uid): ?Document
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $row = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        if ($row === false) {
            return null;
        }

        $objects = $this->dataMapper->map(Document::class, [$row]);
        return $objects[0] ?? null;
    }

    public function search(string $query): array
    {
        // Strip FULLTEXT boolean operators from user input to prevent syntax errors
        $sanitized = preg_replace('/[+\-><()~*"@]+/', ' ', trim($query));
        $words = array_values(array_filter(array_map('trim', explode(' ', $sanitized))));

        if (empty($words)) {
            return [];
        }

        // +word* = word must be present and may have any suffix (prefix match, boolean mode)
        $booleanQuery = implode(' ', array_map(fn(string $w) => '+' . $w . '*', $words));

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $param = $queryBuilder->createNamedParameter($booleanQuery);
        $matchExpression = 'MATCH(headline, markup) AGAINST (' . $param . ' IN BOOLEAN MODE)';

        $rows = $queryBuilder
            ->select('*')
            ->addSelectLiteral($matchExpression . ' AS search_score')
            ->from($this->tableName)
            ->where($matchExpression)
            ->orderBy('search_score', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->dataMapper->map(Document::class, $rows);
    }

    public function update(Document $document): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $queryBuilder
            ->update($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($document->getUid(), Connection::PARAM_INT))
            )
            ->set('headline', $document->getHeadline())
            ->set('markup', $document->getMarkup())
            ->set('type', $document->getType())
            ->set('visibility', $document->getVisibility())
            ->set('parent', (string)($document->getParent()?->getUid() ?? 0))
            ->set('status', (string)($document->getStatus()?->getUid() ?? 0))
            ->set('tstamp', (string)time())
            ->executeStatement();
    }
}
