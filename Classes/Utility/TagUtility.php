<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use PhpCsFixer\DocBlock\Tag;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class TagUtility
 */
class TagUtility
{
    public function getTagBuilder(): TagBuilder
    {
        return GeneralUtility::makeInstance(TagBuilder::class);
    }

    public function buildSvgPlaceHolderImage(float $width, float $height, string $content): string
    {
        $tagBuilder = $this->getTagBuilder();
        $tagBuilder->setTagName('svg');
        $tagBuilder->addAttributes([
            'xmlns' => 'http://www.w3.org/2000/svg',
            'width' => $width,
            'height' => $height
        ]);

        if ($content && $content !== '') {
            if (strpos($content, 'xlink') !== false) {
                // svg tag needs xlink namespace if xlink is used in $content
                $tagBuilder->addAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            }
            $tagBuilder->setContent($content);
        }
        return $tagBuilder->render() ?? '';
    }
}
