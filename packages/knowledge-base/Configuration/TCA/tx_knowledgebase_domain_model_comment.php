<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_comment',
        'label' => 'comment',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'comment',
    ],
    'types' => [
        '1' => [
            'showitem' => 'document, user, comment',
        ],
    ],
    'columns' => [
        'document' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_comment.document',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_knowledgebase_domain_model_document',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'required' => true,
            ],
        ],
        'user' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_comment.user',
            'config' => [
                'type' => 'group',
                'allowed' => 'be_users',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
            ],
        ],
        'comment' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_comment.comment',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'required' => true,
            ],
        ],
    ],
];
