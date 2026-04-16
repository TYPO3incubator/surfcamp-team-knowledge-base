<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Domain\Model\Document;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Service\DocumentService;
use TYPO3Incubator\KnowledgeBase\Service\DocumentTreeService;
use TYPO3Incubator\KnowledgeBase\Service\EmbeddingService;
use TYPO3Incubator\KnowledgeBase\Service\RagService;
use TYPO3Incubator\KnowledgeBase\Service\SearchService;
use TYPO3Incubator\SmartSearch\Service\ModelAvailabilityService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;


#[AsController]
class BackendKnowledgeBaseController extends ActionController
{
    private ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly PageRenderer $pageRenderer,
        protected readonly DocumentTreeService $documentTreeService,
        protected readonly DocumentService $documentService,
        protected readonly RagService $ragService,
        protected readonly EmbeddingService $embeddingService,
        protected readonly SearchService $searchService,
        protected readonly DocumentRepository $documentRepository,
        protected readonly ModelAvailabilityService $modelAvailabilityService,
    ) {}

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->pageRenderer->addCssFile('EXT:knowledge-base/Resources/Public/Css/Backend.css');
        $this->pageRenderer->addCssFile('EXT:knowledge-base/Resources/Public/Css/Modal.css');
        $this->pageRenderer->loadJavaScriptModule('@vendor/typo3-incubator/knowledge-base/Backend.js');
    }

    public function indexAction(): ResponseInterface
    {
        $tree = $this->documentTreeService->getFullTree();
        $openDocumentId = $this->documentTreeService->getOpenDocumentId($tree);
        $this->moduleTemplate->assign('tree', $tree);
        $this->moduleTemplate->assign('openDocumentId', $openDocumentId);
        $openDocument = $this->documentRepository->findByUid($openDocumentId);
        $this->moduleTemplate->assign('openDocumentType', $openDocument?->getType() ?? Document::TYPE_NORMAL);
        $loadChildrenUrl = $this->uriBuilder->reset()->uriFor('loadDocumentChildren', ['documentUid' => 'DOCUMENT_ID_PLACEHOLDER']);
        $this->moduleTemplate->assign('loadChildrenUrl', $loadChildrenUrl);
        $this->moduleTemplate->assign('semanticSearchAvailable', $this->modelAvailabilityService->isEmbeddingServerAvailable());
        $this->moduleTemplate->assign('ragSearchAvailable', $this->modelAvailabilityService->isGenerationServerAvailable());
        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    public function ajaxSearchAction(ServerRequest $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = $params['query'] ?? '';
        $mode = $params['mode'] ?? SearchService::MODE_KEYWORD;
        if (!in_array($mode, SearchService::VALID_MODES, true)) {
            return $this->jsonResponse((string)json_encode([
                'error' => 'Invalid mode. Allowed: ' . implode(', ', SearchService::VALID_MODES),
            ]));
        }

        if ($query === '' && $mode !== SearchService::MODE_KEYWORD) {
            return $this->jsonResponse((string)json_encode([
                'error' => 'Query must not be empty for mode: ' . $mode,
            ]));
        }

        $envelope = match($mode) {
            SearchService::MODE_KEYWORD  => $this->searchService->buildKeywordResults($query),
            SearchService::MODE_SEMANTIC => $this->searchService->buildSemanticResults($query),
            SearchService::MODE_RAG      => $this->searchService->buildRagResults($query),
        };

        return $this->jsonResponse((string)json_encode($envelope));
    }

    public function reindexAction(): ResponseInterface
    {
        if (!$this->modelAvailabilityService->isEmbeddingServerAvailable()) {
            return $this->jsonResponse((string)json_encode(['error' => 'Embedding server is not available']));
        }

        $documents = $this->documentRepository->findAll();
        $count = 0;

        foreach ($documents as $document) {
            $this->embeddingService->generateAndStoreIfChanged($document);
            $count++;
        }

        return $this->jsonResponse((string)json_encode(['reindexed' => $count]));
    }

    public function updateAction(int $documentUid, array $documentData): ResponseInterface
    {
        $result = $this->documentService->updateDocument($documentUid, $documentData);
        if (!$result['success']) {
            $this->addFlashMessage(
                $result['message'] ?? '',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index', null, null, ['openDocumentId' => $documentUid]);
        }
        $this->addFlashMessage(LocalizationUtility::translate('flash.document.updated', 'Knowledge-base') ?? '');
        return $this->redirect('index', null, null, ['openDocumentId' => $documentUid]);
    }

    public function createAction(string $documentHeadline, int $parentId = 0, string $type = 'normal'): ResponseInterface
    {
        $result = $this->documentService->createDocument($documentHeadline, $parentId, $type);

        if (!$result['success']) {
            $this->addFlashMessage(
                $result['message'] ?? '',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return $this->redirect('index');
        }

        $this->addFlashMessage(LocalizationUtility::translate('flash.document.created', 'Knowledge-base') ?? 'Document created.');
        return $this->redirect('index', null, null, ['openDocumentId' => $result['documentUid']]);
    }

    public function ajaxLoadDocumentAction(ServerRequest $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $documentUid = $params['documentUid'] ?? 0;
        $result = $this->documentService->loadDocument((int)$documentUid);
        return $this->jsonResponse((string)json_encode($result));
    }

	public function ajaxLoadDocumentChildrenAction(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams();
		$documentUid = (int)($params['documentUid'] ?? 0);

		$result = $this->documentService->loadDocumentChildren($documentUid);

		return new JsonResponse($result);
	}
}
