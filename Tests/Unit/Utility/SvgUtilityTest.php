<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\SvgUtility;
use C1\AdaptiveImages\Utility\TagUtility;
use PHPUnit\Framework\TestCase;

/**
 * Class SvgUtilityTest
 */
class SvgUtilityTest extends TestCase
{
    /**
     * @test
     */
    public function createPreviewImageTagTest()
    {
        $tagUtility = $this->createMock(TagUtility::class);
        $svgUtility = new SvgUtility($tagUtility);
        $previewImgTag = $svgUtility->createPreviewImageTag('imageUri', 1024, 768);
        $this->assertEquals(
            '<image preserveAspectRatio="xMidYMid slice" xlink:href="imageUri" x="0" y="0" width="1024" height="768"></image>',
            $previewImgTag
        );
    }

    /**
     * @test
     */
    public function getSvgPlaceholderTest()
    {
        $tagUtility = $this->createMock(TagUtility::class);
        $tagUtility
            ->expects(self::once())
            ->method('buildSvgPlaceholderImage')
            ->willReturn('<ImageBase64>');

        $svgUtility = new SvgUtility($tagUtility);
        $placeholder = $svgUtility->getSvgPlaceholder(1024, 768);
        $this->assertEquals(
            'data:image/svg+xml,%3CImageBase64%3E',
            $placeholder
        );
    }
}
