<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Service;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3Incubator\KnowledgeBase\Configuration\LlamaCppConfiguration;

class LlamaCppGenerationClient
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly LlamaCppConfiguration $configuration,
    ) {}

    /**
     * @param array<array{role: string, content: string}> $messages
     */
    public function complete(array $messages): string
    {
        $response = $this->requestFactory->request(
            $this->configuration->getGenerationServerUrl() . '/v1/chat/completions',
            'POST',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(
                    [
                        'messages' => $messages,
                        'max_tokens' => $this->configuration->getGenerationMaxTokens(),
                        'stream' => false,
                    ],
                    JSON_THROW_ON_ERROR
                ),
            ]
        );

        $data = json_decode(
            (string)$response->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $data['choices'][0]['message']['content'] ?? '';
    }
}
