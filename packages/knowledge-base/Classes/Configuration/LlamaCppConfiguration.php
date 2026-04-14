<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class LlamaCppConfiguration
{
    private readonly array $config;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->config = (array)$extensionConfiguration->get('knowledge-base');
    }

    public function getEmbeddingServerUrl(): string
    {
        return (string)($this->config['embeddingServerUrl'] ?? 'http://localhost:8081');
    }

    public function getGenerationServerUrl(): string
    {
        return (string)($this->config['generationServerUrl'] ?? 'http://localhost:8080');
    }

    public function getGenerationMaxTokens(): int
    {
        return (int)($this->config['generationMaxTokens'] ?? 512);
    }

    public function getRagTopK(): int
    {
        return (int)($this->config['ragTopK'] ?? 5);
    }

    public function getDocumentContextLength(): int
    {
        return (int)($this->config['documentContextLength'] ?? 800);
    }

    public function getSemanticThreshold(): float
    {
        return (float)($this->config['semanticThreshold'] ?? 0.30);
    }

    public function getEmbeddingContextLength(): int
    {
        return (int)($this->config['embeddingContextLength'] ?? 1500);
    }
}
