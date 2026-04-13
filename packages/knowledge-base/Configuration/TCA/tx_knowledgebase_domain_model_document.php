<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document',
        'label' => 'headline',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'type' => 'type',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'headline',
    ],
    'types' => [
        'normal' => [
            'showitem' => 'type, headline, markup, parent, user, visibility',
        ],
        'board' => [
            'showitem' => 'type, headline, parent, user, visibility',
        ],
    ],
    'columns' => [
        'headline' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.headline',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'markup' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.markup',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'rows' => 15,
            ],
        ],
        'parent' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_knowledgebase_domain_model_document',
                'foreign_table_where' => 'AND {#tx_knowledgebase_domain_model_document}.{#pid} = ###CURRENT_PID### ORDER BY headline ASC',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'default' => 0,
            ],
        ],
        'user' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.user',
            'config' => [
                'type' => 'group',
                'allowed' => 'be_users',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'visibility' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.visibility',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.visibility.public',
                        'value' => 'public',
                    ],
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.visibility.private',
                        'value' => 'private',
                    ],
                ],
                'default' => 'public',
            ],
        ],
        'type' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.type.normal',
                        'value' => 'normal',
                    ],
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_document.type.board',
                        'value' => 'board',
                    ],
                ],
                'default' => 'normal',
            ],
        ],
    ],
];
