<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\MathUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class MathUtilityTest
 */
class MathUtilityTest extends UnitTestCase
{
    /**
     * @test
     */
    public function calculateRatioReturnsCorrectRatio()
    {
        $utility = new MathUtility();

        $this->assertEquals(100, $utility->calculateRatio(400, 400));
        $this->assertEquals(50, $utility->calculateRatio(200, 400));
        $this->assertEquals(21.77, $utility->calculateRatio(100.25, 460.5));
    }
}
