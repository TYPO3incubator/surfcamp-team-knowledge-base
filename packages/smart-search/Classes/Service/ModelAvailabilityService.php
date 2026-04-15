<?php

declare(strict_types=1);

namespace TYPO3Incubator\SmartSearch\Service;

use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3Incubator\SmartSearch\Configuration\SmartSearchConfiguration;

class ModelAvailabilityService
{
    private ?bool $embeddingAvailable = null;
    private ?bool $generationAvailable = null;

    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly SmartSearchConfiguration $configuration,
    ) {}

    public function isEmbeddingServerAvailable(): bool
    {
        if ($this->embeddingAvailable === null) {
            $this->embeddingAvailable = $this->checkUrl(
                $this->configuration->getEmbeddingServerUrl() . '/health'
            );
        }

        return $this->embeddingAvailable;
    }

    public function isGenerationServerAvailable(): bool
    {
        if ($this->generationAvailable === null) {
            $this->generationAvailable = $this->checkUrl(
                $this->configuration->getGenerationServerUrl() . '/health'
            );
        }

        return $this->generationAvailable;
    }

    private function checkUrl(string $url): bool
    {
        try {
            $response = $this->requestFactory->request($url, 'GET', [
                'timeout' => 2,
                'http_errors' => false,
            ]);

            return $response->getStatusCode() < 300;
        } catch (Throwable) {
            return false;
        }
    }
}
