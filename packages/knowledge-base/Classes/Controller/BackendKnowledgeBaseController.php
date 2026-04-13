<?php

namespace TYPO3Incubator\KnowledgeBase\Controller;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use TYPO3Incubator\KnowledgeBase\Service\DocumentTreeService;
#[AsController]
class BackendKnowledgeBaseController extends ActionController
{
    private ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly DocumentTreeService $documentTreeService
    ) {}

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    public function indexAction(): ResponseInterface
    {
        $tree = $this->documentTreeService->getFullTree();
        $this->moduleTemplate->assign('tree', $tree);
        return $this->moduleTemplate->renderResponse('Backend/Index');
    }
}