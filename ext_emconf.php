<?php
declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'c1 adaptive images',
    'description' => 'ViewHelpers for adaptive images in fluid_styled_content.',
    'category' => 'fe',
    'author' => 'Manuel Munz',
    'author_email' => 't3dev@comuno.net',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.3.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.20-11.9.99',
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
