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
use TYPO3Incubator\KnowledgeBase\Domain\Model\Status;
use TYPO3Incubator\KnowledgeBase\Dto\SlimDocumentDto;

/**
 * @extends Repository<Status>
 */
class StatusRepository extends Repository
{
    private readonly string $tableName;

    public function __construct(
        protected readonly ConnectionPool     $connectionPool,
        protected readonly DataMapper         $dataMapper,
        protected PersistenceManagerInterface $persistenceManager,
        protected readonly Context            $context,
    )
    {
        parent::__construct();
        $this->tableName = $this->dataMapper->getDataMap(Status::class)->getTableName();
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

    public function findByDocumentId(int $documentUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('document', $queryBuilder->createNamedParameter($documentUid, Connection::PARAM_INT))
            )
            ->orderBy('ordering', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
        return $this->dataMapper->map(Status::class, $rows);
    }
}

