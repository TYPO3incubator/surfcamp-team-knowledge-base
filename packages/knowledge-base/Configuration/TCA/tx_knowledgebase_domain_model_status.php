<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_status',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title',
    ],
    'types' => [
        '1' => [
            'showitem' => 'document, title',
        ],
    ],
    'columns' => [
        'document' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_status.document',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_knowledgebase_domain_model_document',
                'foreign_table_where' => 'AND {#tx_knowledgebase_domain_model_document}.{#type} = \'board\' ORDER BY headline ASC',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'required' => true,
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_status.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ],
];
