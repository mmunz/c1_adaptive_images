<?php

namespace C1\ImageRenderer\Tests\Unit\Resource\Rendering;

use C1\ImageRenderer\Resource\Rendering\ImageRenderer;
//use C1\LazysizesImagerenderer\Resource\Rendering\ImageRendererConfiguration;
use Nimut\TestingFramework\TestCase\AbstractTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ImageRendererTest extends AbstractTestCase
{
    /**
     * @var MockObject|ImageRendererConfiguration
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
        $jpgMock = $this->createMock(\TYPO3\CMS\Core\Resource\File::class);
        $jpgMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpg'));

        $jpegMock = $this->createMock(\TYPO3\CMS\Core\Resource\File::class);
        $jpegMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpeg'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($jpgMock));
        $this->assertTrue($imageRenderer->canRender($jpegMock));
    }

    /** @test */
    public function canRenderReturnsTrueOnGif()
    {
        $gifMock = $this->createMock(\TYPO3\CMS\Core\Resource\File::class);
        $gifMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/gif'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($gifMock));
    }

    /** @test */
    public function canRenderReturnsTrueOnPng()
    {
        $pngMock = $this->createMock(\TYPO3\CMS\Core\Resource\File::class);
        $pngMock->expects($this->any())->method('getMimeType')->will($this->returnValue('image/png'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertTrue($imageRenderer->canRender($pngMock));
    }

    /** @test */
    public function canRenderReturnsFalseOnWrongMimeType()
    {
        $audioMock = $this->createMock(\TYPO3\CMS\Core\Resource\File::class);
        $audioMock->expects($this->any())->method('getMimeType')->will($this->returnValue('audio/ogg'));

        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);

        $this->assertFalse($imageRenderer->canRender($audioMock));
    }

    /** @test */
    public function setFileSetsFile()
    {
        $fileMock = $this->createMock(File::class);
        $imageRenderer = new ImageRenderer($this->configurationMock, $this->objectManagerMock);
        $imageRenderer->setFile($fileMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'file', $imageRenderer);
    }

    /** @test */
    public function setFileTransformsFilereferenceToFile()
    {
        $fileMock = $this->createMock(File::class);
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
