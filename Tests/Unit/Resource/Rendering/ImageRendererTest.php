<?php

namespace C1\ImageRenderer\Tests\Unit\Resource\Rendering;

use C1\ImageRenderer\Resource\Rendering\ImageRenderer;
use C1\ImageRenderer\Service\SettingsService;
use Nimut\TestingFramework\TestCase\AbstractTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ImageRendererTest extends AbstractTestCase
{
    /**
     * @var MockObject|SettingsService
     */
    protected $configurationMock;

    /**
     * @var MockObject|objectManager
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        parent::setUp();

        //$this->configurationMock = $this->createMock(ImageRendererConfiguration::class);
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
    }


    /** @test */
    public function getPriorityReturnTheCorrectValue()
    {
        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertSame(5, $imageRenderer->getPriority());
    }

    /** @test */
    public function canRenderReturnsTrueOnJpg()
    {
        /** @var MockObject|File $jpgMock */
        $jpgMock = $this->createMock(File::class);
        $jpgMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpg'));

        /** @var MockObject|File $jpegMock */
        $jpegMock = $this->createMock(File::class);
        $jpegMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpeg'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($jpgMock));
        $this->assertTrue($imageRenderer->canRender($jpegMock));
    }

    /** @test */
    public function canRenderReturnsTrueOnGif()
    {
        /** @var MockObject|File $gifMock */
        $gifMock = $this->createMock(File::class);
        $gifMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/gif'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($gifMock));
    }

    /** @test */
    public function canRenderReturnsTrueOnPng()
    {
        /** @var MockObject|File $pngMock */
        $pngMock = $this->createMock(File::class);
        $pngMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/png'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($pngMock));
    }

    /** @test */
    public function canRenderReturnsFalseOnWrongMimeType()
    {
        /** @var MockObject|File $audioMock */
        $audioMock = $this->createMock(File::class);
        $audioMock->expects($this->any())->method('getMimeType')->will($this->returnValue('audio/ogg'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertFalse($imageRenderer->canRender($audioMock));
    }

    /** @test */
    public function setFileSetsFile()
    {
        /** @var MockObject|File $fileMock */
        $fileMock = $this->createMock(File::class);

        /** @var ImageRenderer $imageRenderer */
        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
        $imageRenderer->setFile($fileMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'file', $imageRenderer);
    }

    /** @test */
    public function setFileTransformsFileReferenceToFile()
    {
        /** @var MockObject|File $fileMock */
        $fileMock = $this->createMock(File::class);

        /** @var MockObject|FileReference $filereferenceMock */
        $filereferenceMock = $this->createMock(FileReference::class);
        $filereferenceMock->expects($this->any())
            ->method('getOriginalFile')
            ->will($this->returnValue($fileMock));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
        $imageRenderer->setFile($filereferenceMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'file', $imageRenderer);
    }

    /** @test */
//    public function setAltTextFromFileAlternative()
//    {
//        $fileMock = $this->createMock(File::class);
//        $fileMock->expects($this->any())
//            ->method('getProperty')
//            ->with('alternative')
//            ->will($this->returnValue('dummy alt text'));
//
//        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
//        $imageRenderer->setFile($fileMock);
//
//        $imageRenderer->setAltText(false);
//
//        Assert::assertAttributeEquals('dummy alt text', 'altText', $imageRenderer);
//    }

    /** @test */
//    public function setAltTextFromFileName()
//    {
//        $fileMock = $this->createMock(File::class);
//
//        $fileMock->expects($this->at(0))
//            ->method('getProperty')
//            ->with('alternative')
//            ->will($this->returnValue(false));
//
//        $fileMock->expects($this->at(1))
//            ->method('getProperty')
//            ->with('name')
//            ->will($this->returnValue('image.jpg'));
//
//        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
//        $imageRenderer->setFile($fileMock);
//
//        $imageRenderer->setAltText(false);
//
//        Assert::assertAttributeEquals('image.jpg', 'altText', $imageRenderer);
//    }

    /** @test */
//    public function setAltTextFromViewHelper()
//    {
//        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
//
//        $imageRenderer->setAltText('custom alt text');
//
//        Assert::assertAttributeEquals('custom alt text', 'altText', $imageRenderer);
//    }
}
