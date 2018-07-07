<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers\Placeholder;

use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\SvgUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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

class SvgViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /** @var bool $escapeOutput */
    protected $escapeOutput = false;

    /**
     * @var ImageUtility;
     */
    protected $imageUtility;

    /**
     * @param ImageUtility $imageUtility
     */
    public function injectImageUtility(ImageUtility $imageUtility)
    {
        $this->imageUtility = $imageUtility;
    }

    /**
     * @var svgUtility;
     */
    protected $svgUtility;

    /**
     * @param SvgUtility $svgUtility
     */
    public function injectSvgUtility(SvgUtility $svgUtility)
    {
        $this->svgUtility = $svgUtility;
    }

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ImageService $imageService
     */
    public function injectImageService(\TYPO3\CMS\Extbase\Service\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

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
                1523050131
            );
        }
        $this->imageUtility->setOriginalFile($image);

        $width = $image->getProperty('width');
        $height = $image->getProperty('height');

        $cropArea = $this->imageUtility->getCropAreaForVariant($this->arguments['cropVariant']);

        if ($cropArea) {
            $width = $cropArea->asArray()['width'];
            $height = $cropArea->asArray()['height'];
        }

        $preview = '';
        if ($this->arguments['embedPreview']) {
            $this->imageUtility->setOriginalFile($image);

            $processingInstructions = [
                'width' => $this->arguments['embedPreviewWidth'],
                'crop' =>$cropArea,
                'additionalParameters' => $this->arguments['embedPreviewAdditionalParameters']
            ];
            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

            $previewImg = sprintf(
                "data:%s;base64,%s",
                $image->getProperty('mime_type'),
                base64_encode($processedImage->getContents())
            );

            $preview = '<image xlink:href="' . $previewImg. '" x="0" y="0" height="100%" width="100%"></image>';
        }

        return $this->svgUtility->getSvgPlaceholder($width, $height, 'transparent', $this->arguments['content'] . $preview);
    }
}
