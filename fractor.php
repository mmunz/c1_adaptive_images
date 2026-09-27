<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3SetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/Configuration',
        __DIR__ . '/Resources',
    ])
    ->withSets([
        Typo3SetList::TYPO3_13,
        Typo3SetList::TYPO3_14,
    ]);
