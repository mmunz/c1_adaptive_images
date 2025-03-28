<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Utility\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Create placeholder images to show while the final image is lazyloaded
 */
class ImagePlaceholderUtility
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly CropVariantUtility $cropVariantUtility
    ) {
    }

    /**
     * Get placeholder image. Either as base64 encoded string or as uri.
     */
    public function getPlaceholderImage(
        FileInterface $file,
        bool $base64,
        string $cropVariant,
        int $width,
        bool $absolute = false
    ): ?string {
        $this->cropVariantUtility->setCropVariantCollection($file);

        $processingInstructions = [
            'width' => $width,
            'crop' => $this->cropVariantUtility->getCropAreaForVariant($cropVariant),
            'additionalParameters' =>
                '-quality 50 -sampling-factor 4:2:0 -strip -colorspace sRGB ' .
                '-unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        ];
        $processedImage = $this->imageService->applyProcessingInstructions($file, $processingInstructions);

        // In some cases (high load, many images have to be generated during a first page call) placeholder images where
        // not generated and the unprocessed image returned. See issue #15.
        // If this happens we return a default placeholder right away.
        if ($this->imageIsProcessed($processedImage) === false) {
            return $this->getFallbackImage($base64);
        }

        if ($processedImage->exists()) {
            if ($base64 === false) {
                return $this->imageService->getImageUri($processedImage, $absolute);
            } else {
                return $this->createInlineImageUri(
                    base64_encode($processedImage->getContents()),
                    $file->getProperty('mime_type')
                );
            }
        } else {
            return $processedImage->getPublicUrl();
        }
    }

    // Return a formatted string for an inline image uri
    public function createInlineImageUri(
        string $base64EncodedImageString,
        string $mimeType
    ): string {
        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            $base64EncodedImageString
        );
    }

    // Return a fallback placeholder image (base64 or url to an image)
    public function getFallbackImage(bool $base64): string
    {
        $pixel = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=';

        if ($base64 === false) {
            return ExtensionManagementUtility::extPath('c1_adaptive_images') . 'Resources/Public/Images/placeholder.png';
        } else {
            return $this->createInlineImageUri($pixel, 'image/png');
        }
    }

    // Test if an image has really been processed
    public function imageIsProcessed(ProcessedFile $processedImage): bool
    {
        // if properties['identifier'] is empty, the image was not processed.
        // Note: $processedImage->getIdentifier() would return the original identifier as fallback, don't use it here.
        $identifier = $processedImage->getProperties()['identifier'] ?? null;
        return !(empty($identifier));
    }
}
