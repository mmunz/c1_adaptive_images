<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'c1 adaptive images',
    'description' => 'ViewHelpers for adaptive images in fluid_styled_content.',
    'category' => 'fe',
    'author' => 'Manuel Munz',
    'author_email' => 't3dev@comuno.net',
    'state' => 'stable',
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
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
