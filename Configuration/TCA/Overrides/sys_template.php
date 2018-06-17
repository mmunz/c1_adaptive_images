<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'c1_adaptive_images',
    'Configuration/TypoScript',
    'image renderer for fluid styled content'
);
