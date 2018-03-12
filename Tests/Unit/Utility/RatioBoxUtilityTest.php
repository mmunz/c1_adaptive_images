<?php

namespace C1\ImageRenderer\Tests\Unit\Utility;

use C1\ImageRenderer\Utility\RatioBoxUtility;
use Nimut\TestingFramework\TestCase\AbstractTestCase;
use TYPO3\CMS\Core\Page\PageRenderer;

class RatioBoxUtilityTest extends AbstractTestCase
{

    /**
     * @var MockObject|PageRenderer
     */
    protected $pageRendererMock;

    protected function setUp()
    {
        parent::setUp();

        //$this->configurationMock = $this->createMock(ImageRendererConfiguration::class);
        $this->pageRendererMock = $this->createMock(PageRenderer::class);
    }

    /** @test */
    public function sanitizeCssClassnameWorksAsExpected()
    {
        $utility = new RatioBoxUtility($this->pageRendererMock);
        $this->assertEquals('test-test', $utility->sanitizeCssClassname('TEST_test!?$%&'));
    }

    /** @test */
    public function getRatioClassForCropVariantReturnsValidClass() {
        $utility = new RatioBoxUtility($this->pageRendererMock);
        $utility->setRatioBoxBase('ratio-box');

        $this->assertEquals('ratio-box--42', $utility->getRatioClassForCropVariant(42));
        $this->assertEquals(
            'ratio-box-max-width768px--42',
            $utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );

        // with media query
        $this->assertEquals(
            'ratio-box-max-width768px--42',
            $utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );

        // now with different ratioBoxBase
        $utility->setRatioBoxBase('rbx');
        $this->assertEquals('rbx--42', $utility->getRatioClassForCropVariant(42));
        $this->assertEquals(
            'rbx-max-width768px--42',
            $utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );
    }

    /** @test */
    public function getRatioBoxStyleReturnsCorrectStyle()
    {
        $utility = new RatioBoxUtility($this->pageRendererMock);
        $utility->setRatioBoxBase('ratio-box');

        $this->assertEquals('.ratio-box--42{padding-bottom:42%}', $utility->getRatioBoxStyle(42));
        $this->assertEquals('.ratio-box--42dot23{padding-bottom:42.23%}', $utility->getRatioBoxStyle(42.23));

        // with media query
        $this->assertEquals(
            '@media max-width: 768px{.ratio-box-max-width768px--42{padding-bottom:42%}}',
            $utility->getRatioBoxStyle(42, 'max-width: 768px'));
        $this->assertEquals(
            '@media max-width: 768px{.ratio-box-max-width768px--42dot23{padding-bottom:42.23%}}',
            $utility->getRatioBoxStyle(42.23, 'max-width: 768px'));

        // with changed ratioBoxBase
        $utility->setRatioBoxBase('rbx');
        $this->assertEquals(
            '@media max-width: 768px{.rbx-max-width768px--42{padding-bottom:42%}}',
            $utility->getRatioBoxStyle(42, 'max-width: 768px'));
    }

    /** @test */
    public function getRatioBoxClassnamesReturnsCorrectClassnames()
    {
        $utility = new RatioBoxUtility($this->pageRendererMock);
        $utility->setRatioBoxBase('ratio-box');

        $cropVariants = [
            'mobile' => [
                'media' => '(max-width:767px)',
                'ratio' => 75
            ],
            'default' => [
                'ratio' => 56.25
            ]
        ];

        $this->assertEquals(
            Array (
                0 => 'ratio-box',
                1 => 'ratio-box--56dot25',
                2 => 'ratio-box-max-width767px--75'
            ),
            $utility->getRatioBoxClassnames($cropVariants)
        );

        // with changed ratioBoxBase
        $utility = new RatioBoxUtility($this->pageRendererMock);
        $utility->setRatioBoxBase('rbx');
        $this->assertEquals(
            Array (
                0 => 'rbx',
                1 => 'rbx--56dot25',
                2 => 'rbx-max-width767px--75'
            ),
            $utility->getRatioBoxClassnames($cropVariants)
        );
    }




//
//    /** @test */
//    public function getDebugInformationFillsInWidthAndHeight()
//    {
//        $utility = new RatioBoxUtility();
//
//        $this->assertEquals(
//            '-pointsize 40 -stroke white -gravity Center -annotate +0+0 "400m x 400" -gravity NorthWest',
//            $utility->getDebugAnnotation('400m', '400')
//        );
//    }

//    /** @test */
// MOVE TO ImageUtilityTest
//    public function calculateRatioReturnsCorrectRatio()
//    {
//        $utility = new RatioBoxUtility();
//
//        $this->assertEquals(100, $utility->calculateRatio(400, 400));
//        $this->assertEquals(50, $utility->calculateRatio(200, 400));
//        $this->assertEquals(21.77, $utility->calculateRatio(100.25, 460.5));
//    }

//
}
