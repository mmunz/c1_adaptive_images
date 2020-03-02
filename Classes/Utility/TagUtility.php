<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class TagUtility
 */
class TagUtility
{
    /**
     * Build the ratio box tag
     * @param string $content
     * @param array $classNames
     * @return string
     */
    public function buildRatioBoxTag(string $content, array $classNames)
    {
        $tagBuilder = new TagBuilder('div', $content);
        $tagBuilder->setTagName('div');
        $tagBuilder->setContent($content);
        $tagBuilder->addAttribute('class', implode(' ', $classNames));
        return $tagBuilder->render();
    }
}
