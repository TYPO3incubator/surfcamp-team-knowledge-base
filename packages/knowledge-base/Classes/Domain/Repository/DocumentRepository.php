<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Repository;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Dto\SlimDocumentDto;

/**
 * @extends Repository<Document>
 */
class DocumentRepository extends Repository
{
    private readonly string $tableName;

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly DataMapper $dataMapper,
        protected PersistenceManagerInterface $persistenceManager,
        protected readonly Context $context,
    ) {
        parent::__construct();
        $this->tableName = $this->dataMapper->getDataMap(Document::class)->getTableName();
    }

    public function initializeObject(): void
    {
        $this->defaultQuerySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }

    public function getTableName(): string
    {
        return $this->tableName;
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

    public function fetchSlimNodesByParent(int $parentIdentifier): array
    {
        $backendUserUid = $this->context->getPropertyFromAspect('backend.user', 'id');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('uid', 'parent', 'visibility', 'type', 'headline')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentIdentifier, Connection::PARAM_INT)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('visibility', $queryBuilder->createNamedParameter('public', Connection::PARAM_STR)),
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('visibility', $queryBuilder->createNamedParameter('private', Connection::PARAM_STR)),
                        $queryBuilder->expr()->eq('user', $queryBuilder->createNamedParameter($backendUserUid, Connection::PARAM_INT))
                    )
                )
            )
            ->orderBy('headline', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->dataMapper->map(SlimDocumentDto::class, $rows);
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

    public function update($document): void
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

    public function getChildren(int $documentUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($documentUid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAllAssociative();
        return $this->dataMapper->map(Document::class, $rows);
    }

    /**
     * @param int[] $uids
     * @return Document[]
     */
    public function findByUids(array $uids): array
    {
        if (empty($uids)) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        if (empty($rows)) {
            return [];
        }

        // Preserve the order of $uids
        $indexed = [];
        foreach ($this->dataMapper->map(Document::class, $rows) as $document) {
            $indexed[$document->getUid()] = $document;
        }

        $ordered = [];
        foreach ($uids as $uid) {
            if (isset($indexed[$uid])) {
                $ordered[] = $indexed[$uid];
            }
        }
        return $ordered;
    }
}

