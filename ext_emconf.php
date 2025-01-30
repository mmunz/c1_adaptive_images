<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'c1 adaptive images',
    'description' => 'ViewHelpers for adaptive images in fluid_styled_content.',
    'category' => 'fe',
    'author' => 'Manuel Munz',
    'author_email' => 't3dev@comuno.net',
    'state' => 'stable',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'C1\\AdaptiveImages\\' => 'Classes',
        ]
    ]
];
