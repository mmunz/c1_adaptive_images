<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\ViewHelpers\Placeholder\ImageViewHelper;

/**
 * Class ImageViewHelperTest
 */
class ImageViewHelperTest extends \C1\AdaptiveImages\Tests\Unit\ViewHelpers\AbstractViewHelperTest
{

    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new ImageViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->inject($this->viewHelper, 'imageService', $this->mockImageService());
        $cropVariantUtility = $this->getMockBuilder(CropVariantUtility::class)->getMock();
        $this->inject($this->viewHelper, 'cropVariantUtility', $cropVariantUtility);
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
            'file-uri' => [
                [
                    'dataUri' => false,
                    'width' => '192',
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ])
                ],
                '/image@192.jpg'
            ],
            'data-uri' => [
                [
                    'width' => '192',
                    'dataUri' => true,
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ])
                ],
                'data:jpg;base64,dGhlIGltYWdlcyBjb250ZW50'
            ],
            'without_dataUri_argument_should render_data-uri' => [
                [
                    'width' => '192',
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ])
                ],
                'data:jpg;base64,dGhlIGltYWdlcyBjb250ZW50'
            ],
        ];
    }

    /**
     * @test
     * @param array $arguments
     * @param string $expected
     * @dataProvider renderProvider
     */
    public function render($arguments, $expected)
    {
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
        $this->assertEquals($expected, $this->viewHelper->initializeArgumentsAndRender());
    }
}
