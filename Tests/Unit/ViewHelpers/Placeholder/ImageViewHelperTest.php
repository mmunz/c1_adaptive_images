<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\ViewHelpers\Placeholder\ImageViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Class ImageViewHelperTest
 * @package C1\AdaptiveImages\Tests\Unit\ViewHelpers
 */
class ImageViewHelperTest extends \C1\AdaptiveImages\Tests\Unit\ViewHelpers\AbstractViewHelperTest
{

    /** @var ViewHelperInterface */
    protected $utility;

    /**
     * set up
     */
    protected function setUp()
    {
        /** @var RenderingContext $renderingContext */
        //$renderingContext = $this->getMock(RenderingContext::class);


        $this->utility = new ImageViewHelper;

        $this->injectDependenciesIntoViewHelper($this->utility);

//        $this->utility->setRenderingContext($renderingContext);
//        $this->setArgumentsUnderTest(
//            $this->utility
//        );


        $this->inject($this->utility, 'imageService', $this->mockImageService());
        $this->inject($this->utility, 'imageUtility', $this->mockImageUtility());
        $this->inject($this->utility, 'objectManager', $this->mockObjectManager());
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
                        'height' => '768'
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
                        'height' => '768'
                    ])
                ],
                'data:;base64,dGhlIGltYWdlcyBjb250ZW50'
            ],
            'without_dataUri_argument_should render_data-uri' => [
                [
                    'width' => '192',
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768'
                    ])
                ],
                'data:;base64,dGhlIGltYWdlcyBjb250ZW50'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider renderProvider
     */
    public function render($arguments, $output)
    {
        $this->setArgumentsUnderTest($this->utility, $arguments);
        $this->assertEquals($output, $this->utility->render());
    }
}
