<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class SvgUtility
 */
class SvgUtility
{

    /**
     *  Get a SVG Placeholder image as placeholder
     *
     * @param int $width
     * @param int $height
     * @param string $content
     *
     * @return string
     *
     */
    public function getSvgPlaceholder($width = 100, $height = 75, $content = '')
    {
        $svgTag = new TagBuilder('svg');

        $svgTag->addAttributes([
            'xmlns' => 'http://www.w3.org/2000/svg',
            'width' => $width,
            'height' => $height
        ]);

        if ($content && $content !== '') {
            if (strpos($content, 'xlink') !== false) {
                // svg tag needs xlink namespace if xlink is used in $content
                $svgTag->addAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
            }
            $svgTag->setContent($content);
        }

        $dataImage = 'data:image/svg+xml,' . rawurlencode($svgTag->render());

        return $dataImage;
    }
}
