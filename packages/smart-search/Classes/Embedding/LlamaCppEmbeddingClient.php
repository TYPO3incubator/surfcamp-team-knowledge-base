<?php

declare(strict_types=1);

namespace TYPO3Incubator\SmartSearch\Embedding;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3Incubator\SmartSearch\Configuration\SmartSearchConfiguration;

class LlamaCppEmbeddingClient implements EmbeddingClientInterface
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly SmartSearchConfiguration $configuration,
    ) {}

    /**
     * @return float[]
     */
    public function embed(string $text): array
    {
        // Retry with progressively shorter text if the server rejects the input
        // as too long (HTTP 400). Each attempt halves the text.
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $response = $this->requestFactory->request(
                $this->configuration->getEmbeddingServerUrl() . '/embedding',
                'POST',
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode(['content' => $text], JSON_THROW_ON_ERROR),
                    'http_errors' => false,
                ]
            );

            if ($response->getStatusCode() !== 400) {
                break;
            }

            $text = mb_substr($text, 0, (int)(mb_strlen($text) / 2));
        }

        $data = json_decode(
            (string)$response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $data[0]['embedding'][0] ?? [];
    }
}
