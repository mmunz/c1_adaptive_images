<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Utility\Placeholder;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * ImagePlaceholderUtility
 * Create placeholder images to show while the final image is lazyloaded
 *
 */
class ImagePlaceholderUtility
{
    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     * @inject
     */
    protected $imageService;

    /**
     * @var \C1\AdaptiveImages\Utility\CropVariantUtility
     * @inject
     */
    protected $cropVariantUtility;

    /**
     * getBase64EncodedImage
     *
     * @param FileInterface $file
     * @param bool $base64
     * @param string $cropVariant
     * @param int $width
     */
    public function getPlaceholderImage($file, $base64, $cropVariant, $width, $height = 0)
    {
        $imageUri = null;
        $this->cropVariantUtility->setCropVariantCollection($file);

        $processingInstructions = [
            'width' => $width,
            'height' => $height,
            'crop' => $this->cropVariantUtility->getCropAreaForVariant($cropVariant),
            'additionalParameters' =>
                '-quality 50 -sampling-factor 4:2:0 -strip -colorspace sRGB ' .
                '-unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        ];
        $processedImage = $this->imageService->applyProcessingInstructions($file, $processingInstructions);

        if ($base64 === false) {
            $imageUri = $this->imageService->getImageUri($processedImage, $this->arguments['absolute']);
            return $imageUri;
        } else {
            return sprintf(
                'data:%s;base64,%s',
                $file->getProperty('mime_type'),
                base64_encode($processedImage->getContents())
            );
        }
    }
}
