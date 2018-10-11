<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

/**
 * Class MathUtility
 */
class MathUtility
{

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
        return round($height / $width * 100, $precision);
    }
}
