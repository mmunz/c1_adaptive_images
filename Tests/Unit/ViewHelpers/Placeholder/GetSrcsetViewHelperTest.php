<?php
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers\Placeholder;

use C1\AdaptiveImages\ViewHelpers\GetSrcsetViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperInterface;

class GetSrcsetViewHelperTest extends \C1\AdaptiveImages\Tests\Unit\ViewHelpers\AbstractViewHelperTest
{

    /** @var ViewHelperInterface */
    protected $utility;

    /**
     * set up
     */
    protected function setUp()
    {
        $this->utility = new GetSrcsetViewHelper;
        $this->inject($this->utility, 'imageService', $this->mockImageService());
        //$this->inject($this->utility, 'imageUtility', $this->mockImageUtility());
        $this->inject($this->utility, 'objectManager', $this->mockObjectManager());
    }

    /**
     * @return array
     *
     * array of test data for the viewHelpers render() method.
     *
     * Every entry is an arry and contains:
     *
     * 1. viewHelper arguments
     * 2. expected return value from the viewHelper
     *
     */
    public function renderProvider()
    {
        return [
            'width_and_file' => [
                [
                    'widths' => '240,320,480',
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768'
                    ])
                ],
                '/image@240.jpg 240w,/image@320.jpg 320w,/image@480.jpg 480w'
            ],
            'width_and_file_with_debug_enabled' => [
                [
                    'widths' => '240,320,480',
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768'
                    ]),
                    'debug' => false,
                ],
                '/image@240.jpg 240w,/image@320.jpg 320w,/image@480.jpg 480w'
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
        $this->assertEquals($output, $this->utility->render());
    }

}
