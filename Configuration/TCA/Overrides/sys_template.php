<?php
declare(strict_types=1);
defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'c1_adaptive_images',
    'Configuration/TypoScript',
    'adaptive image viewhelpers for fluid styled content'
);
