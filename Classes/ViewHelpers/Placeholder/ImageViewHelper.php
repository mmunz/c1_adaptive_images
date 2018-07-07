<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers\Placeholder;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Create placeholder images for lazyloading
 *
 * = Examples =
 *
 * <code title="Default">
 * <ai:image.placeholder file="EXT:myext/Resources/Public/typo3_logo.png" width="50px" base64="1" />
 * </code>
 * <output>
 * data:image/png+xml;base64,... base64 encoded image ...
 * </output>
 *
 * <code title="ImageURI">
 * <ai:image.placeholder file="EXT:myext/Resources/Public/typo3_logo.png" width="50px" base64="1" dataUri="0" />
 * </code>
 * <output>
 * /path/to/processed/file.png
 * </output>
 *
 */

class ImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var \C1\AdaptiveImages\Utility\ImageUtility
     * @inject
     */
    protected $imageUtility;

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     * @inject
     */
    protected $imageService;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', '\TYPO3\CMS\Core\Resource\FileInterface', 'File or FileReference', true);
        $this->registerArgument(
            'cropVariant',
            'string',
            'cropVariant to use. (Default: "default")',
            false,
            'default'
        );
        $this->registerArgument(
            'width',
            'string',
            'width of the image. This can be a numeric value representing the fixed width of the image in ' .
            'pixels. But you can also perform simple calculations by adding "m" or "c" to the value. ' .
            'See imgResource.width for possible options.',
            false,
            128
        );
        $this->registerArgument(
            'height',
            'string',
            'height of the image. This can be a numeric value representing the fixed height of the image ' .
            'in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See ' .
            'imgResource.width for possible options. Leave empty to keep aspect ratio of the original image.'
        );
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
        $this->registerArgument('dataUri', 'bool', 'Return data-uri', false, true);
    }

    /**
     * Resizes a given image (if required) and returns either of
     *
     * - data-uri string with base64 encoded image (default) or
     * - url to the image
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render()
    {

        /** @var FileInterface $image */
        $image = $this->arguments['file'];
        $imageUri = null;

        if (is_null($image)) {
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
                'You must specify a File object.',
                1522176433
            );
        }
        $this->imageUtility->setOriginalFile($image);

        $processingInstructions = [
            'width' => $this->arguments['width'],
            'height' => $this->arguments['height'],
            'crop' =>$this->imageUtility->getCropAreaForVariant($this->arguments['cropVariant']),
            'additionalParameters' =>
                '-quality 50 -sampling-factor 4:2:0 -strip -posterize 136 -colorspace sRGB ' .
                '-unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        ];

        $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

        if ($this->arguments['dataUri'] !== false) {
            return sprintf(
                "data:%s;base64,%s",
                $image->getProperty('mime_type'),
                base64_encode($processedImage->getContents())
            );
        } else {
            $imageUri = $this->imageService->getImageUri($processedImage, $this->arguments['absolute']);
            return $imageUri;
        }
    }
}
