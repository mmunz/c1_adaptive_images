<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\ImageUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ImageUtilityTest
 */
class ImageUtilityTest extends UnitTestCase
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
        'srcsetWidths' => '240,360,480,660,840,1024,1280,1440,1680,1920',
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

    /**
     * @test
     */
    public function setOriginalFileSetsFile()
    {
        /** @var File $fileMock */
        $fileMock = $this->createMock(File::class);
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);
        $utility->setOriginalFile($fileMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'originalFile', $utility);
    }
}
