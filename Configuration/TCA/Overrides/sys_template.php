<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'c1_imagerenderer',
    'Configuration/TypoScript',
    'image renderer for fluid styled content'
);
