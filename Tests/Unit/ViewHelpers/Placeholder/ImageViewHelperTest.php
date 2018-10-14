<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\ViewHelpers\Placeholder\ImageViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Class ImageViewHelperTest
 */
class ImageViewHelperTest extends AbstractViewHelperTest
{
    protected $resetSingletonInstances = true;

    /**
     * @var ViewHelperInterface
     */
    protected $viewHelper;

    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new ImageViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $this->inject($this->viewHelper, 'imageService', $this->mockImageService());
        $this->inject($this->viewHelper, 'imageUtility', $this->mockImageUtility());
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
     * @dataProvider renderProvider
     */
    public function render($arguments, $output)
    {
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
        $this->assertEquals($output, $this->viewHelper->initializeArgumentsAndRender());
    }
}
