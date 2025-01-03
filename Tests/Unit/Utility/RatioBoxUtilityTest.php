<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\RatioBoxUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Class RatioBoxUtilityTest
 */
class RatioBoxUtilityTest extends TestCase
{
    protected RatioBoxUtility $utility;

    protected MockObject $pageRendererMock;

    protected MockObject $ratioBoxUtilityMock;

    protected CropVariantUtility $cropVariantUtilityMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRendererMock = $this->createMock(PageRenderer::class);
        $this->cropVariantUtilityMock = $this->createMock(CropVariantUtility::class);
        $this->utility = new RatioBoxUtility($this->pageRendererMock, $this->cropVariantUtilityMock);
    }

    /**
     * @test
     */
    public function sanitizeCssClassNameWorksAsExpected()
    {
        $this->assertEquals('test-test', $this->utility->sanitizeCssClassName('TEST_test!?$%&'));
    }

    /**
     * @test
     */
    public function getRatioClassForCropVariantReturnsValidClass()
    {
        $this->utility->setRatioBoxBase('ratio-box');

        $this->assertEquals('ratio-box--42', $this->utility->getRatioClassForCropVariant(42));
        $this->assertEquals(
            'ratio-box--max-width768px-42',
            $this->utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );

        // with media query
        $this->assertEquals(
            'ratio-box--max-width768px-42',
            $this->utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );

        // now with different ratioBoxBase
        $this->utility->setRatioBoxBase('rbx');
        $this->assertEquals('rbx--42', $this->utility->getRatioClassForCropVariant(42));
        $this->assertEquals(
            'rbx--max-width768px-42',
            $this->utility->getRatioClassForCropVariant(42, 'max-width: 768px')
        );
    }

    /**
     * @test
     */
    public function getRatioBoxStyleReturnsCorrectStyle()
    {
        $this->utility->setRatioBoxBase('ratio-box');

        $this->assertEquals('.ratio-box--42{padding-bottom:42%}', $this->utility->getRatioBoxStyle(42));
        $this->assertEquals('.ratio-box--42dot23{padding-bottom:42.23%}', $this->utility->getRatioBoxStyle(42.23));

        // with media query
        $this->assertEquals(
            '@media max-width: 768px{.ratio-box.ratio-box--max-width768px-42{padding-bottom:42%}}',
            $this->utility->getRatioBoxStyle(42, 'max-width: 768px')
        );
        $this->assertEquals(
            '@media max-width: 768px{.ratio-box.ratio-box--max-width768px-42dot23{padding-bottom:42.23%}}',
            $this->utility->getRatioBoxStyle(42.23, 'max-width: 768px')
        );

        // with changed ratioBoxBase
        $this->utility->setRatioBoxBase('rbx');
        $this->assertEquals(
            '@media max-width: 768px{.rbx.rbx--max-width768px-42{padding-bottom:42%}}',
            $this->utility->getRatioBoxStyle(42, 'max-width: 768px')
        );
    }

    /**
     * @test
     */
    public function getRatioBoxClassNamesReturnsCorrectClassNames()
    {
        $this->utility->setRatioBoxBase('ratio-box');

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
            [
                0 => 'ratio-box',
                1 => 'ratio-box--56dot25',
                2 => 'ratio-box--max-width767px-75'
            ],
            $this->utility->getRatioBoxClassNames($cropVariants)
        );

        // with changed ratioBoxBase
        $this->utility->setRatioBoxBase('rbx');
        $this->assertEquals(
            [
                0 => 'rbx',
                1 => 'rbx--56dot25',
                2 => 'rbx--max-width767px-75'
            ],
            $this->utility->getRatioBoxClassNames($cropVariants)
        );
    }
}
