<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Embedding;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3Incubator\KnowledgeBase\Configuration\LlamaCppConfiguration;

class LlamaCppEmbeddingClient implements EmbeddingClientInterface
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly LlamaCppConfiguration $configuration,
    ) {}

    /**
     * @return float[]
     */
    public function embed(string $text): array
    {
        $response = $this->requestFactory->request(
            $this->configuration->getEmbeddingServerUrl() . '/embedding',
            'POST',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['content' => $text], JSON_THROW_ON_ERROR),
            ]
        );

        $data = json_decode(
            (string)$response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $data[0]['embedding'][0] ?? [];
    }
}
