<?php

declare(strict_types=1);

namespace TYPO3Incubator\SmartSearch\Embedding;

interface EmbeddingClientInterface
{
    /**
     * @return float[]
     */
    public function embed(string $text): array;
}
