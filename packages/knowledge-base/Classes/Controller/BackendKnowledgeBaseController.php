<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Incubator\KnowledgeBase\Service\DocumentService;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3Incubator\KnowledgeBase\Domain\Repository\DocumentRepository;
use TYPO3Incubator\KnowledgeBase\Service\DocumentTreeService;

#[AsController]
class BackendKnowledgeBaseController extends ActionController
{
    private ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly PageRenderer $pageRenderer,
        protected readonly DocumentTreeService $documentTreeService,
        protected readonly DocumentService $documentService,
        protected readonly DocumentRepository $documentRepository,
        protected readonly PersistenceManager $persistenceManager,
        protected readonly BackendUserRepository $backendUserRepository,
    ) {}

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->pageRenderer->addCssFile('EXT:knowledge-base/Resources/Public/Css/Backend.css');
    }

    public function indexAction(int $openDocumentId = 0): ResponseInterface
    {
        $tree = $this->documentTreeService->getFullTree();
        $this->moduleTemplate->assign('tree', $tree);
        $this->moduleTemplate->assign('openDocumentId', $openDocumentId);
        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    public function searchAction(string $query = ''): ResponseInterface
    {
        $results = $this->documentService->searchDocuments($query);
        return $this->jsonResponse((string)json_encode($results));
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

    public function createAction(string $documentHeadline, int $parentId, string $visibility): ResponseInterface
    {
        $result = $this->documentService->createDocument($documentHeadline, $parentId, $visibility);

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
}