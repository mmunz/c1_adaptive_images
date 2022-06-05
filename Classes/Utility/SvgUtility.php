<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

/**
 * Class SvgUtility
 */
class SvgUtility
{
    protected TagUtility $tagUtility;

    public function __construct(TagUtility $tagUtility)
    {
        $this->tagUtility = $tagUtility;
    }

    // Get a SVG Placeholder image as placeholder
    public function getSvgPlaceholder(float $width = 100, float $height = 75, string $content = ''): string
    {
        $svgTag = $this->tagUtility->buildSvgPlaceholderImage($width, $height, $content) ?? '';
        return 'data:image/svg+xml,' . rawurlencode($svgTag);
    }

    // Create preview svg image tag
    public function createPreviewImageTag(string $img, float $width, float $height): string
    {
        return sprintf(
            '<image preserveAspectRatio="xMidYMid slice" xlink:href="%s" x="0" y="0" width="%s" height="%s"></image>',
            $img,
            $width,
            $height
        );
    }
}
