<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use C1\AdaptiveImages\Utility\SvgUtility;
use C1\AdaptiveImages\Utility\TagUtility;
use C1\AdaptiveImages\ViewHelpers\Placeholder\SvgViewHelper;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\Rendering\RenderingContextFixture;

/**
 * Class ImageViewHelperTest
 */
class SvgViewHelperTest extends \C1\AdaptiveImages\Tests\Unit\ViewHelpers\AbstractViewHelperTest
{
    /**
     * set up
     */
    protected function setUp(): void
    {
        parent::setUp();

        $cropVariantUtility = new CropVariantUtility(new MathUtility());
        //$tagUtility = $this->createMock(TagUtility::class);
        $svgUtility = new SvgUtility(new TagUtility());
//        $svgUtility = $this->createMock(SvgUtility::class);
//        $svgUtility->expects(self::once())
//            ->method('getSvgPlaceholder')
//            ->willReturn('foo');

        $imageServiceMock = $this->mockImageService();
        $this->viewHelper = new SvgViewHelper($imageServiceMock, $svgUtility, $cropVariantUtility);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function testInitializeArguments()
    {
        /** @var AccessibleMockObjectInterface|SvgViewHelper $instance */
        $instance = $this->getAccessibleMock(SvgViewHelper::class, ['registerArgument'], [
            $this->mockImageService(),
            $this->createMock(SvgUtility::class),
            $this->createMock(CropVariantUtility::class)
        ]);
        $instance->expects($this->at(0))->method('registerArgument')->with(
            'file',
            '\TYPO3\CMS\Core\Resource\FileInterface',
            $this->anything(),
            true
        );
        $instance->expects($this->at(1))->method('registerArgument')->with(
            'cropVariant',
            'string',
            $this->anything(),
            false,
            'default'
        );
        $instance->expects($this->at(2))->method('registerArgument')->with(
            'content',
            'string',
            $this->anything(),
            false,
            ''
        );
        $instance->expects($this->at(3))->method('registerArgument')->with(
            'embedPreview',
            'boolean',
            $this->anything(),
            false,
            false
        );
        $instance->expects($this->at(4))->method('registerArgument')->with(
            'embedPreviewWidth',
            'integer',
            $this->anything(),
            false,
            64
        );
        $instance->expects($this->at(5))->method('registerArgument')->with(
            'embedPreviewAdditionalParameters',
            'string',
            $this->anything(),
            false,
            '-quality 50 -sampling-factor 4:2:0 -strip -posterize 136 -colorspace sRGB -unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        );
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function exceptionWhenNoFileGiven()
    {
        $arguments = [];
        $this->expectExceptionCode(1237823699);
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
    }

    /**
     * @return array
     *
     *
     * array of test data for the viewHelpers render() method.
     *
     * Every entry is an array and contains:
     *
     * 1. viewHelper arguments
     * 2. expected return value from the viewHelper
     *
     */
    public function renderProvider()
    {
        return [
            'empty-svg' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => 1200,
                        'height' => 768,
                        'mime_type' => 'jpg'
                    ]),
                ],
                'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22768%22%20%2F%3E'
            ],
            'svg-with-preview' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => 1200,
                        'height' => 768,
                        'mime_type' => 'jpg'
                    ]),
                    'embedPreview' => true
                ],
                'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22768%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%3E%3Cimage%20preserveAspectRatio%3D%22xMidYMid%20slice%22%20xlink%3Ahref%3D%22data%3Ajpg%3Bbase64%2CdGhlIGltYWdlcyBjb250ZW50%22%20x%3D%220%22%20y%3D%220%22%20width%3D%221200%22%20height%3D%22768%22%3E%3C%2Fimage%3E%3C%2Fsvg%3E'
            ],
            'svg-with-preview-and-cropVariants' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => 1200,
                        'height' => 768,
                        'mime_type' => 'jpg',
                        'crop' => '{"default":{"cropArea":{"height":0.8992,"width":1,"x":0,"y":0.0096},"selectedRatio":"16:9","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}},"mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}',
                    ]),
                    'cropVariant' => 'default',
                    'embedPreview' => true
                ],
                'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22690.5856%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%3E%3Cimage%20preserveAspectRatio%3D%22xMidYMid%20slice%22%20xlink%3Ahref%3D%22data%3Ajpg%3Bbase64%2CdGhlIGltYWdlcyBjb250ZW50%22%20x%3D%220%22%20y%3D%220%22%20width%3D%221200%22%20height%3D%22690.5856%22%3E%3C%2Fimage%3E%3C%2Fsvg%3E'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider renderProvider
     *
     * @param array $arguments
     * @param string $expected
     */
    public function renderTest($arguments, $expected)
    {
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
        $result = $this->viewHelper->render();
        $this->assertEquals($expected, $result);
    }
}
