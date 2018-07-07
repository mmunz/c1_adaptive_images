<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class SvgUtility
 * @package C1\AdaptiveImages\Utility
 */
class SvgUtility
{

    /**
     *  Get a SVG Placeholder image as placeholder
     *
     * @param int $width
     * @param int $height
     * @param string $backgroundColor
     * @param string $content
     *
     * @return string
     *
     */
    public function getSvgPlaceholder($width = 100, $height = 75, $backgroundColor = 'transparent', $content = '')
    {
        $svg = sprintf(
            "<svg style='background-color: %s' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='%s' height='%s' viewBox='0 0 %s %s'>",
            $backgroundColor,
            $width,
            $height,
            $width,
            $height
        );
        $svg .= $content;
        $svg .= "</svg>";

        $dataImage = "data:image/svg+xml;base64," . base64_encode($svg);

        return $dataImage;
    }
}
