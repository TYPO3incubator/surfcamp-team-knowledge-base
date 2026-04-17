<?php

use TYPO3Incubator\KnowledgeBase\Controller\BackendKnowledgeBaseController;

return [
    'knowledge-base' => [
        'parent' => 'content',
        'position' => ['top'],
        'access' => 'user',
        'workspaces' => 'live',
        'navigationComponent' => '',
        'inheritNavigationComponentFromMainModule' => false,
        'labels' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'KnowledgeBase',
        'iconIdentifier' => 'tx-knowledge-base-module',
        'controllerActions' => [
            BackendKnowledgeBaseController::class => [
                'index',
                'ajaxSearch',
                'reindex',
                'update',
                'create',
                'delete',
                'ajaxLoadDocument',
                'ajaxLoadDocumentChildren',
                'createStatus',
                'ajaxUpdateStatus',
            ],
        ],
    ],
];
