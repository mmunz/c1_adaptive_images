<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\ViewHelpers\Placeholder\SvgViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Class ImageViewHelperTest
 * @package C1\AdaptiveImages\Tests\Unit\ViewHelpers
 */
class SvgViewHelperTest extends AbstractViewHelperTest
{

    /**
     * @var ViewHelperInterface
     */
    protected $utility;

    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
        // reset singletons in testing framework after each test
        $this->resetSingletonInstances = true;

        $this->utility = new SvgViewHelper();

        $this->inject($this->utility, 'imageService', $this->mockImageService());
        $this->inject($this->utility, 'imageUtility', $this->mockImageUtility());
        $this->inject($this->utility, 'svgUtility', $this->mockSvgUtility());
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
            'empty-svg' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ])
                ],
                'data:image/svg+xml;base64,ABCDEFG...'
            ],
            'svg-with-preview' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ]),
                    'embedPreview' => true
                ],
                'data:image/svg+xml;base64,ABCDEFG...with_content...'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider renderProvider
     */
    public function render($arguments, $output)
    {
        $this->utility->setArguments($arguments);
        $this->utility->validateArguments();
        $this->assertEquals($output, $this->utility->render());
    }
}
