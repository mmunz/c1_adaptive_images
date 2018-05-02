<?php

namespace C1\ImageRenderer\Utility;

class SvgUtility
{

    /**
     *  Get a SVG Placeholder image as placeholder
     *
     * @param int $width
     * @param int $height
     * @param string $backgroundColor
     *
     * @return string
     *
     */
    public function getSvgPlaceholder($width = 100, $height = 75, $backgroundColor = 'transparent')
    {
        $svg = sprintf(
            "<svg xmlns='http://www.w3.org/2000/svg' width='%s' height='%s' viewBox='0 0 %s %s'>",
            $width,
            $height,
            $width,
            $height
        );


        $svg .= sprintf(
            "<rect width='100%%' height='100%%' fill='%s'/></svg>",
            $backgroundColor
        );

        $dataImage = "data:image/svg+xml;base64," . base64_encode($svg);
        return $dataImage;
    }
}
