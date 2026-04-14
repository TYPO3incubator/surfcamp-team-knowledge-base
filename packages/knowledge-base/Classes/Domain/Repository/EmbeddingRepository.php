<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

class EmbeddingRepository
{
    private const TABLE = 'tx_knowledgebase_domain_model_embedding';
    private const DOCUMENT_TABLE = 'tx_knowledgebase_domain_model_document';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * @param float[] $vector
     */
    public function upsert(int $documentUid, array $vector, string $contentHash): void
    {
        $existing = $this->findRowByDocumentUid($documentUid);
        $now = time();

        if ($existing !== null) {
            $this->connectionPool
                ->getConnectionForTable(self::TABLE)
                ->update(
                    self::TABLE,
                    [
                        'vector' => json_encode($vector, JSON_THROW_ON_ERROR),
                        'content_hash' => $contentHash,
                        'tstamp' => $now,
                    ],
                    ['document' => $documentUid],
                    [Connection::PARAM_STR, Connection::PARAM_STR, Connection::PARAM_INT, Connection::PARAM_INT]
                );
        } else {
            $this->connectionPool
                ->getConnectionForTable(self::TABLE)
                ->insert(
                    self::TABLE,
                    [
                        'document' => $documentUid,
                        'vector' => json_encode($vector, JSON_THROW_ON_ERROR),
                        'content_hash' => $contentHash,
                        'tstamp' => $now,
                    ],
                    [Connection::PARAM_INT, Connection::PARAM_STR, Connection::PARAM_STR, Connection::PARAM_INT]
                );
        }
    }

    public function findContentHashByDocumentUid(int $documentUid): ?string
    {
        $row = $this->findRowByDocumentUid($documentUid);
        return $row !== null ? (string)$row['content_hash'] : null;
    }

    /**
     * Returns all embeddings for non-deleted, non-hidden documents.
     * Result is an array of ['document_uid' => int, 'vector' => float[]] entries.
     *
     * @return array<array{document_uid: int, vector: float[]}>
     */
    public function findAll(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $rows = $queryBuilder
            ->select('e.document', 'e.vector')
            ->from(self::TABLE, 'e')
            ->join(
                'e',
                self::DOCUMENT_TABLE,
                'd',
                $queryBuilder->expr()->eq('e.document', 'd.uid')
            )
            ->where(
                $queryBuilder->expr()->eq('d.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('d.hidden', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static function (array $row): array {
            return [
                'document_uid' => (int)$row['document'],
                'vector' => json_decode((string)$row['vector'], true, 512, JSON_THROW_ON_ERROR),
            ];
        }, $rows);
    }

    private function findRowByDocumentUid(int $documentUid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $row = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('document', $queryBuilder->createNamedParameter($documentUid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row !== false ? $row : null;
    }
}
