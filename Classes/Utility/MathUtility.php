<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Utility;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MathUtility
 */
class MathUtility implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     *
     * Calculates the ratio of an image.
     *
     * Returns a float which is the percentage of height compared to the width
     * Rounded to 2 decimals by default.
     *
     * @param int|float $height
     * @param int|float $width
     * @param int $precision
     * @return float
     */
    public function calculateRatio($height, $width, int $precision = 2)
    {
        // Corrupted or empty images don't have width or height or it is 0 which caused division by zero errors, see #17.
        // This should ideally be handled before calling this method.
        if (!$width || !$height || $width == 0) {
            $this->logger->warning(
                'Invalid width or height, cannot calculate the ratio. Return 0.00 instead. '
                . 'This most likely means there is a corrupt or empty image.',
                [
                    'width' => $width,
                    'height' => $height
                ]
            );
            return round(0, $precision);
        }
        return round($height / $width * 100, $precision);
    }
}
