<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class TagUtility
{
    public function getTagBuilder(): TagBuilder /** @phpstan-ignore-line */
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
            if (str_contains($content, 'xlink')) {
                // svg tag needs xlink namespace if xlink is used in $content
                $tagBuilder->addAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            }
            $tagBuilder->setContent($content);
        }
        return $tagBuilder->render();
    }
}
