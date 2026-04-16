<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Service\EmbeddingService;
use TYPO3Incubator\SmartSearch\Service\ModelAvailabilityService;

class ReIndexDocumentEmbeddingsCommand extends Command
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly EmbeddingService $embeddingService,
        private readonly ModelAvailabilityService $modelAvailabilityService,
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setDescription('Task for re-indexing the documents embedding database for the smart search');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->modelAvailabilityService->isEmbeddingServerAvailable()) {
            $output->writeln('<error>Embedding server is not available</error>');
            return Command::FAILURE;
        }

        $updatedDocumentsCount = $this->reindexAllDocumentEmbeddings();
        $output->writeln('Updated documents: ' . $updatedDocumentsCount);
        return Command::SUCCESS;
    }

    private function reindexAllDocumentEmbeddings(): int
    {
        $documents = $this->documentRepository->findAll();
        $count = 0;

        foreach ($documents as $document) {
            $this->embeddingService->generateAndStoreIfChanged($document);
            $count++;
        }

        return $count;
    }
}
