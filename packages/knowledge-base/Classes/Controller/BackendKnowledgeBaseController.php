<?php

namespace TYPO3Incubator\KnowledgeBase\Controller;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use TYPO3Incubator\KnowledgeBase\Tree\DocumentTreeProvider;
#[AsController]
class BackendKnowledgeBaseController extends ActionController
{
    private ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly DocumentTreeProvider $documentTreeProvider
    ) {}

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    public function indexAction(): ResponseInterface
    {
        $tree = $this->documentTreeProvider->getFullTree();
        $this->moduleTemplate->assign('tree', $tree);
        $this->moduleTemplate->assign('treeNodes', array_fill(0, 100, 0));
        return $this->moduleTemplate->renderResponse('Backend/Index');
    }
}