<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Utility\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * ImagePlaceholderUtility
 * Create placeholder images to show while the final image is lazyloaded
 *
 */
class ImagePlaceholderUtility
{
    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @var \C1\AdaptiveImages\Utility\CropVariantUtility
     */
    protected $cropVariantUtility;


    public function __construct(ImageService $imageService, CropVariantUtility $cropVariantUtility)
    {
        $this->imageService = $imageService;
        $this->cropVariantUtility = $cropVariantUtility;
    }

    /**
     * getBase64EncodedImage
     *
     * @param FileInterface $file
     * @param bool $base64
     * @param string $cropVariant
     * @param int $width
     * @param bool $absolute
     * @return string|null
     */
    public function getPlaceholderImage($file, $base64, $cropVariant, $width, $absolute = false)
    {
        $imageUri = null;
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
            $pixel = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=';

            if ($base64 === false) {
                $imageUri = ExtensionManagementUtility::extPath('c1_adaptive_images') . 'Resources/Public/Images/placeholder.png';
                return $imageUri;
            } else {
                return $this->createInlineImageUri($pixel, 'image/png');
            }
        }

        if ($processedImage->exists()) {
            if ($base64 === false) {
                $imageUri = $this->imageService->getImageUri($processedImage, $absolute);
                return $imageUri;
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

    /**
     * @param string $base64EncodedImageString
     * @param string $mimeType
     * @return string
     */
    public function createInlineImageUri($base64EncodedImageString, $mimeType)
    {
        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            $base64EncodedImageString
        );
    }

    /**
     * @param ProcessedFile $processedImage
     * @return bool
     */
    public function imageIsProcessed($processedImage)
    {
        // if properties['identifier'] is empty, the image was not processed.
        // Note: $processedImage->getIdentifier() would return the original identifier as fallback, don"t use it here.
        $identifier = $processedImage->getProperties()['identifier'];
        $hasIdentifier = !(empty($identifier));
        return $hasIdentifier;
    }
}
