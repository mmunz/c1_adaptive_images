<?php

namespace C1\ImageRenderer\Tests\Unit\Utility;

use C1\ImageRenderer\Utility\ImageUtility;
use Nimut\TestingFramework\TestCase\AbstractTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ImageUtilityTest extends AbstractTestCase
{

    /**
     * @var MockObject|objectManager
     */
    protected $objectManagerMock;

    /**
     * @var array $settingsMock
     */
    protected $settingsMock = [
        'debug' => '0',
        'defaultImgWidth' => '768',
        'srcsetWidths' => '240,360,480,660,840,1024,1280,1440,1680,1920',
        'cssClasses' => [
            'img' => 'img-responsive lazyload',
            'ratioBoxBase' => 'ratio-box',
            'ratioBoxAdditional' => ''
        ]
    ];

    /**
     * @var array optionsMock
     */
    protected $optionsMock = [
        'additionalAttributes' => null,
        'data' => null,
        'class' => 'image-embed-item',
        'dir' => null,
        'id' => null,
        'lang' => null,
        'style' => null,
        'title' => '',
        'accesskey' => null,
        'tabindex' => null,
        'onclick' => null,
        'alt' => '',
        'file' => null,
        'width' => 2560,
        'height' => 1475,
        'cropVariant' => 'default',
        'renderMode' => 'fluidtemplate',
        'cropVariants' => [
            'mobile' => [
                'srcsetWidths' => '320,640',
                'media' => '(max-width:767px)'
            ]
        ],
        'debugImgProperties' => 'TRUE'
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
    }

    /** @test */
    public function setOriginalFileSetsFile()
    {
        /** @var File $fileMock */
        $fileMock = $this->createMock(File::class);
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);
        $utility->setOriginalFile($fileMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'originalFile', $utility);
    }


    /** @test */
    public function getDebugInformationReturnsAnnotationForImageMagick()
    {
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);

        $this->assertEquals(
            '-pointsize 30 -gravity Center \
                        -fill black -annotate +0+0 "400m x 400 (0.5)" -gravity NorthWest -pointsize 30 ' .
                        '-gravity Center -fill white -annotate +1+1 "400m x 400 (0.5)" -gravity NorthWest',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'ImageMagick')
        );
    }

    /** @test */
    public function getDebugInformationReturnsAnnotationForGraphicsMagick()
    {
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);

        $this->assertEquals(
            '-pointsize 30 -gravity center -fill white -draw "text 10,20 \'400m x 400 (0.5)\'"',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'GraphicsMagick')
        );
    }

    /** @test */
    public function getDebugInformationReturnsEmptyStringForUnknownProcessor()
    {
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);

        $this->assertEquals(
            '',
            $utility->getDebugAnnotation('400m', '400', 0.5, 'NoMagick')
        );
    }


    /** @test */
    public function calculateRatioReturnsCorrectRatio()
    {
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);

        $this->assertEquals(100, $utility->calculateRatio(400, 400));
        $this->assertEquals(50, $utility->calculateRatio(200, 400));
        $this->assertEquals(21.77, $utility->calculateRatio(100.25, 460.5));
    }

    /** @test */
    public function getCropVariantsReturnsCropVariants()
    {
        /** @var MockObject|ImageUtility $mock */
        $mock = $this->getMockBuilder(ImageUtility::class)
            ->setConstructorArgs([$this->optionsMock, $this->settingsMock, $this->objectManagerMock])
            ->setMethods(['processSrcsetImages'])
            ->getMock();

        $candidates = [
            'mobile' => [
                '320' => [
                    'url' => 'file-320.jpg',
                    'width' => 320,
                    'ratio' => 0.75
                ],
                '640' => [
                    'url' => 'file-640.jpg',
                    'width' => 640,
                    'ratio' => 0.75
                ],
            ],
            'default' => [
                '600' => [
                    'url' => 'file-600.jpg',
                    'width' => 600,
                    'ratio' => 0.5
                ],
                '992' => [
                    'url' => 'file-992.jpg',
                    'width' => 992,
                    'ratio' => 0.5
                ],
            ],
        ];

        $expectation = [
            'mobile' => [
                'srcsetWidths' => '320,640',
                'media' => '(max-width:767px)',
                'candidates' => $candidates['mobile'],
                'srcset' => 'file-320.jpg 320w,file-640.jpg 640w',
                'ratio' => 0.75
            ],
            'default' => array (
                'srcsetWidths' => '600,992',
                'candidates' => $candidates['default'],
                'srcset' => 'file-600.jpg 600w,file-992.jpg 992w',
                'ratio' => 0.5
            )
        ];

        $mock->expects($this->at(0))
            ->method('processSrcsetImages')
            ->will($this->returnValue($candidates['mobile']));

        $mock->expects($this->at(1))
            ->method('processSrcsetImages')
            ->will($this->returnValue($candidates['default']));


        $this->assertEquals(
            $expectation,
            $mock->getCropVariants()
        );
    }
}
