<?php

namespace C1\AdaptiveImages\Tests\Unit\Utility\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImagePlaceholderUtilityTest extends TestCase
{
    /** @var MockObject|ImageService */
    private $imageService;
    /** @var MockObject|CropVariantUtility */
    private $cropVariantUtility;

    protected function setUp(): void
    {
        $this->imageService = $this->createMock(ImageService::class);
        $this->cropVariantUtility = $this->createMock(CropVariantUtility::class);
    }

    public function testGetPlaceholderImageReturnsAFallbackImageIfImageIsNotProcessed(): void
    {
        $file = $this->createMock(FileInterface::class);
        $cropVariant = '';
        $width = '1024';
        $this->cropVariantUtility
            ->expects(self::once())
            ->method('setCropVariantCollection')
            ->with($file);

        $this->cropVariantUtility
            ->expects(self::once())
            ->method('getCropAreaForVariant')
            ->with($cropVariant)
            ->willReturn($cropVariant);

        $image = $this->createMock(ProcessedFile::class);
        $this->imageService
            ->expects(self::once())
            ->method('applyProcessingInstructions')
            ->with(
                $file,
                self::callback(
                    fn ($value) => is_array($value)
                    && $value['width'] === $width
                    && $value['crop'] === $cropVariant
                )
            )
            ->willReturn($image);

        $placeHolderUtility = new ImagePlaceholderUtility($this->imageService, $this->cropVariantUtility);

        $placeholder = $placeHolderUtility->getPlaceholderImage($file, true, $cropVariant, $width);

        self::assertStringContainsString('base64', $placeholder);
        self::assertStringContainsString('image/png', $placeholder);
    }

    public function testGetPlaceholderImage(): void
    {
        $image = $this->createMock(ProcessedFile::class);
        $image->method('getProperties')->willReturn(['identifier' => 'not empty']);
        $image->method('exists')->willReturn(false);
        $image->method('getPublicUrl')->willReturn('image_url');

        $this->imageService->method('applyProcessingInstructions')->willReturn($image);

        $placeHolderUtility = new ImagePlaceholderUtility($this->imageService, $this->cropVariantUtility);

        $placeholder = $placeHolderUtility->getPlaceholderImage($this->createMock(FileInterface::class), true, '', '1024');

        self::assertEquals('image_url', $placeholder);
    }

    public function testCreateInlineImageUriReturnsAProperFormattedString(): void
    {
        $placeHolderUtility = new ImagePlaceholderUtility($this->imageService, $this->cropVariantUtility);

        $result = $placeHolderUtility->createInlineImageUri('test', 'foo');

        self::assertEquals('data:foo;base64,test', $result);
    }

    public function testItCanDetermineIfTheImageWasProcessed(): void
    {
        $image = $this->createMock(ProcessedFile::class);
        $image->method('getProperties')->willReturn(['identifier' => 'not empty']);
        $placeHolderUtility = new ImagePlaceholderUtility($this->imageService, $this->cropVariantUtility);

        self::assertTrue($placeHolderUtility->imageIsProcessed($image));
    }
}
