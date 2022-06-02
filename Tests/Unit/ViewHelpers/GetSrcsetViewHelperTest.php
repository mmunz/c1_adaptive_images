<?php

namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\DebugUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use C1\AdaptiveImages\ViewHelpers\GetSrcsetViewHelper;
use Nimut\TestingFramework\Rendering\RenderingContextFixture;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Class GetSrcsetViewHelperTest
 */
class GetSrcsetViewHelperTest extends AbstractViewHelperTest
{
    protected $runTestInSeparateProcess = false;

    /**
     * @var bool Reset singletons created by subject
     */
    protected $resetSingletonInstances = true;

    /** @var GetSrcsetViewHelper */
    protected $viewHelper;

    /**
     * set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        $imageServiceMock = $this->mockImageService();
        $mathUtility = new MathUtility();
        $debugUtility = new DebugUtility();
        $this->viewHelper = new GetSrcsetViewHelper($imageServiceMock, $mathUtility, $debugUtility);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function testInitializeArguments()
    {
        $instance = $this->getAccessibleMock(GetSrcsetViewHelper::class, ['registerArgument'], [
            $this->mockImageService(),
            new MathUtility(),
            new DebugUtility()
        ]);
        $instance->expects($this->at(0))->method('registerArgument')->with('file', FileInterface::class, $this->anything(), true);
        $instance->expects($this->at(1))->method('registerArgument')->with('widths', 'string', $this->anything(), false, [320, 640, 1024, 1440, 1920]);
        $instance->expects($this->at(2))->method('registerArgument')->with('cropVariant', 'string', $this->anything(), false, 'default');
        $instance->expects($this->at(3))->method('registerArgument')->with('debug', 'bool', $this->anything(), false, false);
        $instance->expects($this->at(4))->method('registerArgument')->with('absolute', 'bool', $this->anything(), false, false);
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @return array
     */
    public function createSrcsetStringProvider()
    {
        return [
            'with 1 srcset width' => [
                [
                    'widths' => '240',
                    'debug' => false,
                ],
                'image@240.jpg 240w'
            ],
            // If only one width is given in the viewhelpers arguments then it might be converted to integer.
            'with 1 srcset width given as int' => [
                [
                    'widths' => 240,
                    'debug' => false,
                ],
                'image@240.jpg 240w'
            ],
            'with 3 srcset widths' => [
                [
                    'widths' => '240,320,480',
                    'debug' => false,
                ],
                'image@240.jpg 240w,image@320.jpg 320w,image@480.jpg 480w'
            ],
        ];
    }

    /**
     * @test
     * @param array $arguments
     * @param string $expected
     * @dataProvider createSrcsetStringProvider
     */
    public function createSrcsetString($arguments, $expected)
    {
        $properties = [
            'width' => '1200',
            'height' => '768',
            'crop' => '',
            'absolute' => true
        ];

        $image = $this->mockFileObject($properties);

        $originalFile = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originalFile->expects($this->any())->method('getProperties')->willReturn([]);
        $this->inject($image, 'originalFile', $originalFile);
        $this->inject($image, 'propertiesOfFileReference', []);

        $imageService = $this->getMockBuilder(ImageService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getImage', 'applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageService
            ->expects($this->any())
            ->method('applyProcessingInstructions')
            ->with($image, $this->anything())
            ->willReturnCallback(function ($image, $instructions) use ($properties) {
                $instructions = array_merge($properties, $instructions);
                // no upscaling of images
                // height: 111 is just a placeholder
                $instructions = [
                    'height' => 111,
                    'width' => min(intval($image->getProperty('width')), intval($instructions['width']))
                ];
                // mocked "processed" image
                return $this->mockProcessedFileObject($instructions);
            });

        $imageService
            ->expects($this->any())
            ->method('getImageUri')
            ->willReturnCallback(function ($image, $absolute) {
                return (($absolute) ? 'http://domain.tld/' : '') . 'image@' . $image->getProperty('width') . '.jpg';
            });

        $arguments['file'] = $image;
        $this->inject($this->viewHelper, 'imageService', $imageService);
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
        $result = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals($expected, $result);
    }
}
