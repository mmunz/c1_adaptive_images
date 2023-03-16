<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\DebugUtility;
use Codeception\PHPUnit\TestCase;

/**
 * Class DebugUtilityTest
 */
class DebugUtilityTest extends TestCase
{
    /**
     * @test
     */
    public function getDebugInformationReturnsAnnotationForImageMagick()
    {
        $utility = new DebugUtility();

        $this->assertEquals(
            '-pointsize 30 -gravity Center -fill black -annotate +0+0 "400m x 400 (0.5)" -gravity ' .
                        'NorthWest -pointsize 30 -gravity Center -fill white -annotate +2+2 "400m x 400 (0.5)" ' .
                        '-gravity NorthWest',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'ImageMagick')
        );
    }

    /**
     * @test
     */
    public function getDebugInformationReturnsAnnotationForGraphicsMagick()
    {
        $utility = new DebugUtility();

        $this->assertEquals(
            '-pointsize 30 -gravity center -fill white -draw "text 10,20 \'400m x 400 (0.5)\'"',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'GraphicsMagick')
        );
    }

    /**
     * @test
     */
    public function getDebugInformationReturnsEmptyStringForUnknownProcessor()
    {
        $utility = new DebugUtility();

        $this->assertEquals(
            '',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'NoMagick')
        );
    }

//    /** @test */
//    public function calculateRatioReturnsCorrectRatio()
//    {
//        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);
//
//        $this->assertEquals(100, $utility->calculateRatio(400, 400));
//        $this->assertEquals(50, $utility->calculateRatio(200, 400));
//        $this->assertEquals(21.77, $utility->calculateRatio(100.25, 460.5));
//    }
}
