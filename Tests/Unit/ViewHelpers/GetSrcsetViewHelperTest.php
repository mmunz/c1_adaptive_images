<?php

namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\ViewHelpers\GetSrcsetViewHelper;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
/**
 * Class GetSrcsetViewHelperTest
 * @package C1\AdaptiveImages\Tests\Unit\ViewHelpers
 */
class GetSrcsetViewHelperTest extends ViewHelperBaseTestcase
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
    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new GetSrcsetViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @return array
     */
    public function getInvalidArguments()
    {
        return [
            [
                [
                'file' => 'foo',
                ],
                'exception' => \InvalidArgumentException::class,
                'code' => 1256475113,
                'message' => 'The argument "file" was registered with type "object", but is of type "string" in view helper "C1\AdaptiveImages\ViewHelpers\GetSrcsetViewHelper"'
            ],
            [
                [
                    'absolute' => "1",
                ],
                'exception' => \TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class,
                'code' => 1522176433,
                'message' => "You must specify a File object"
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getInvalidArguments
     * @param array $arguments
     * @param object $exception
     * @param int $code
     * @param string $message
     */
    public function renderMethodThrowsExceptionOnInvalidArguments(array $arguments, $exception, $code, $message)
    {
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);

        $this->expectException($exception);
        $this->expectExceptionCode($code);
        $this->expectExceptionMessage($message);

        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function exceptionWhenNoFileArgument()
    {
        $arguments = [
            'widths' => '240,320,480',
            'file' => null,
            'debug' => true,
        ];

        $this->setArgumentsUnderTest($this->viewHelper, $arguments);

        $exception = null;

        try {
            $result = $this->viewHelper->initializeArgumentsAndRender();
        } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $e) {
            $exception = $e;
            $this->assertEquals(1522176433, $exception->getCode());
            $this->assertEquals("You must specify a File object.", $exception->getMessage());
        }
        $this->assertNotNull($exception);
    }

    /**
     * @test
     */
    public function createSrcsetString()
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
                return $this->mockFileObject($instructions);
            });

        $imageService
            ->expects($this->any())
            ->method('getImageUri')
            ->willReturnCallback(function ($image, $absolute) {
                return (($absolute) ? 'http://domain.tld/' : '') . 'image@' . $image->getProperty('width') . '.jpg';
            });

        $this->inject($this->viewHelper, 'imageService', $imageService);


        $arguments = [
            'widths' => '240,320,480',
            'file' => $image,
            'debug' => true,
        ];
        $this->setArgumentsUnderTest($this->viewHelper, $arguments);
        //$this->viewHelper->validateArguments();

        $result = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('image@240.jpg 240w,image@320.jpg 320w,image@480.jpg 480w', $result);
    }

    public function mockFileObject($properties)
    {
        $image = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty', 'setProperty', 'getContents'])
            ->getMock();

        $image->expects($this->any())
            ->method('getProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                return $properties[$property];
            });

        $image->expects($this->any())
            ->method('getContents')
            ->willReturnCallback(function () {
                return "the images content";
            });

        return $image;
    }
}
