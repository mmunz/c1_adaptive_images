<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

class DebugUtility
{
    /**
     * returns a string with debug information for additionalParameters of a processing configuration
     */
    public function getDebugAnnotation(int|string $width, int|string $height, float|int $ratio, ?string $processor = null): string
    {
        if (!$processor) {
            $processor = $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'] ?? null;
        }

        if ($processor === 'GraphicsMagick') {
            return sprintf(
                '-pointsize 30 -gravity center -fill white -draw "text 10,20 \'%s x %s (%s)\'"',
                $width,
                $height,
                $ratio
            );
        }

        if ($processor === 'ImageMagick') {
            $text = sprintf(
                '-pointsize 30 -gravity Center -fill black -annotate +0+0 "%s x %s (%s)" -gravity NorthWest ',
                $width,
                $height,
                $ratio
            );

            $text .= sprintf(
                '-pointsize 30 -gravity Center -fill white -annotate +2+2 "%s x %s (%s)" -gravity NorthWest',
                $width,
                $height,
                $ratio
            );
            return $text;
        }
        return '';
    }
}
