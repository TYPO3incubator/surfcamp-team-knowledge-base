<?php

declare(strict_types=1);

use TYPO3Incubator\KnowledgeBase\Controller\BackendKnowledgeBaseController;

return [
    'loadDocumentChildren' => [
        'path' => '/knowledgebase/loadDocumentChildren',
        'target' => BackendKnowledgeBaseController::class . '::ajaxLoadDocumentChildrenAction',
    ],
    'searchDocuments' => [
        'path' => '/knowledgebase/searchDocuments',
        'target' => BackendKnowledgeBaseController::class . '::ajaxSearchAction',
    ],
];
