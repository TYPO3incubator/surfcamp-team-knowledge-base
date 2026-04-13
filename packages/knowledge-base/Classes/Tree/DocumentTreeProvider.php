<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tree;

use TYPO3\CMS\Backend\Dto\Tree\Label\Label;
use TYPO3\CMS\Backend\Dto\Tree\TreeItem;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class DocumentTreeProvider
{
    private readonly string $tableName;

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly IconFactory $iconFactory,
        protected readonly DataMapper $dataMapper
    ) {
        $this->tableName = $this->dataMapper->getDataMap(Document::class)->getTableName();
    }

    /**
     * @return array
     */
    public function getFullTree(): array
    {
        return $this->getNodesRecursive(0, 0);
    }

    /**
     * @return array
     */
    protected function getNodesRecursive(int $parentIdentifier, int $depth): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentIdentifier, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->orderBy('headline', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $items = [];
        foreach ($rows as $row) {
            $item = [
                'identifier' => (string)$row['uid'],
                'parentIdentifier' => (string)$row['parent'],
                'name' => (string)($row['headline'] ?: 'Document ' . $row['uid']),
                'depth' => $depth,
                'children' => $this->getNodesRecursive((int)$row['uid'], $depth + 1),
            ];
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return TreeItem[]
     */
    public function getRootNodes(): array
    {
        return $this->getNodes(0, 0);
    }

    /**
     * @return TreeItem[]
     */
    public function getSubNodes(int $parentIdentifier, int $depth): array
    {
        return $this->getNodes($parentIdentifier, $depth);
    }

    /**
     * @return TreeItem[]
     */
    protected function getNodes(int $parentIdentifier, int $depth): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $rows = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentIdentifier, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->orderBy('headline', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $items = [];
        foreach ($rows as $row) {
            $items[] = new TreeItem(
                identifier: (string)$row['uid'],
                parentIdentifier: (string)$row['parent'],
                recordType: $this->tableName,
                name: (string)($row['headline'] ?: 'Document ' . $row['uid']),
                prefix: '',
                suffix: '',
                tooltip: (string)$row['headline'],
                depth: $depth,
                hasChildren: $this->hasChildren((int)$row['uid']),
                loaded: false,
                icon: $this->iconFactory->getIconForRecord($this->tableName, $row, IconSize::SMALL)->getIdentifier(),
                overlayIcon: '',
            );
        }

        return $items;
    }

    protected function hasChildren(int $uid): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        return (bool)$queryBuilder
            ->count('uid')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($uid, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchOne();
    }
}
