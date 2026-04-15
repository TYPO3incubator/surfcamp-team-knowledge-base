<?php

declare(strict_types=1);

use TYPO3Incubator\KnowledgeBase\Controller\BackendKnowledgeBaseController;

return [
    'loadDocumentChildren' => [
        'path' => '/knowledgebase/loadDocumentChildren',
        'target' => BackendKnowledgeBaseController::class . '::ajaxLoadDocumentChildrenAction',
    ],
    'loadDocument' => [
        'path' => '/knowledgebase/loadDocument',
        'target' => BackendKnowledgeBaseController::class . '::ajaxLoadDocumentAction',
    ],
    'searchDocuments' => [
        'path' => '/knowledgebase/searchDocuments',
        'target' => BackendKnowledgeBaseController::class . '::ajaxSearchAction',
    ],
];
