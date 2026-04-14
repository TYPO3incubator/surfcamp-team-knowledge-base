<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction',
        'label' => 'reaction',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:knowledge-base/Resources/Public/Icons/Reactions.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'document, user, reaction',
        ],
    ],
    'columns' => [
        'document' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.document',
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
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.user',
            'config' => [
                'type' => 'group',
                'allowed' => 'be_users',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
            ],
        ],
        'reaction' => [
            'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.reaction',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.reaction.like',
                        'value' => 'like',
                    ],
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.reaction.heart',
                        'value' => 'heart',
                    ],
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.reaction.thumbs_down',
                        'value' => 'thumbs_down',
                    ],
                    [
                        'label' => 'LLL:EXT:knowledge-base/Resources/Private/Language/locallang_db.xlf:tx_knowledgebase_domain_model_reaction.reaction.celebrate',
                        'value' => 'celebrate',
                    ],
                ],
                'required' => true,
            ],
        ],
    ],
];
