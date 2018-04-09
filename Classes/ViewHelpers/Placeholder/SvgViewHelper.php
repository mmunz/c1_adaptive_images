<?php
namespace C1\ImageRenderer\ViewHelpers\Placeholder;

use C1\ImageRenderer\Utility\ImageUtility;
use C1\ImageRenderer\Utility\SvgUtility;

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

        $this->registerArgument('image', '\TYPO3\CMS\Core\Resource\FileInterface', 'File or FileReference', true);
        $this->registerArgument(
            'cropVariant',
            'string',
            'cropVariant to use. (Default: "default")',
            false,
            'default'
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
        $image = $this->arguments['image'];
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

        return $this->svgUtility->getSvgPlaceholder($width, $height);
    }
}
