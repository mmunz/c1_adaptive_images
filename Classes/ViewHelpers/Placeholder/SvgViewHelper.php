<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers\Placeholder;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\SvgUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Create a placeholder svg for lazyloading
 *
 * = Examples =
 *
 * <code title="Default">
 * <ai:svg.placeholder file="{image}" />
 * </code>
 * <output>
 * data:image/svg+xml;base64,<base 64 encoded svg>
 * </output>
 *
 */
class SvgViewHelper extends AbstractViewHelper
{
    /** @var bool $escapeOutput */
    protected $escapeOutput = false;

    /**
     * @var ImageService
     */
    protected $imageService;

    /**
     * @var SvgUtility $svgUtility;
     */
    protected $svgUtility;

    /**
     * @var CropVariantUtility $cropVariantUtility
     */
    protected $cropVariantUtility;

    public function __construct(ImageService $imageService, SvgUtility $svgUtility, CropVariantUtility $cropVariantUtility)
    {
        $this->imageService = $imageService;
        $this->svgUtility = $svgUtility;
        $this->cropVariantUtility = $cropVariantUtility;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'file',
            '\TYPO3\CMS\Core\Resource\FileInterface',
            'File or FileReference',
            true
        );
        $this->registerArgument(
            'cropVariant',
            'string',
            'cropVariant to use. (Default: "default")',
            false,
            'default'
        );
        $this->registerArgument(
            'content',
            'string',
            'A string added inside the SVG tag',
            false,
            ''
        );
        $this->registerArgument(
            'embedPreview',
            'boolean',
            'Embed small version of the image as preview inside the SVG.',
            false,
            false
        );
        $this->registerArgument(
            'embedPreviewWidth',
            'integer',
            'width of the embedded preview image.',
            false,
            64
        );
        $this->registerArgument(
            'embedPreviewAdditionalParameters',
            'string',
            'additional parameters to pass to IM/GM when rendering the preview image.',
            false,
            '-quality 50 -sampling-factor 4:2:0 -strip -posterize 136 -colorspace sRGB ' .
            '-unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        );
    }

    /**
     * Resizes a given image (if required) and returns either of
     *
     * - data-uri string with base64 encoded image (default) or
     * - url to the image
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws Exception
     * @return string Rendered tag
     */
    public function render()
    {
        /** @var FileInterface $image */
        $image = $this->arguments['file'];
        $width = (float) $image->getProperty('width');
        $height = (float) $image->getProperty('height');
        $this->cropVariantUtility->setCropVariantCollection($image);
        $cropArea = $this->cropVariantUtility->getCropAreaForVariant($this->arguments['cropVariant']);

        if ($cropArea) {
            $width = (float) $cropArea->asArray()['width'];
            $height = (float) $cropArea->asArray()['height'];
        }

        $preview = '';

        if ($this->arguments['embedPreview']) {
            $processingInstructions = [
                'width' => $this->arguments['embedPreviewWidth'],
                'crop' => $cropArea,
                'additionalParameters' => $this->arguments['embedPreviewAdditionalParameters']
            ];
            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

            $previewImg = sprintf(
                'data:%s;base64,%s',
                $image->getProperty('mime_type'),
                base64_encode($processedImage->getContents())
            );

            $preview = $this->svgUtility->createPreviewImageTag($previewImg, $width, $height);
        }
        return $this->svgUtility->getSvgPlaceholder($width, $height, $this->arguments['content'] . $preview);
    }
}
