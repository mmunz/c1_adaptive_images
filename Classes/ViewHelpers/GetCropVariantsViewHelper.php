<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
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
 * data:image/svg+xml;base64,... base64 encoded image ...
 * </output>
 *
 */
class GetCropVariantsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @var \C1\AdaptiveImages\Utility\ImageUtility
     */
    protected $imageUtility;

    /**
     * @param \C1\AdaptiveImages\Utility\ImageUtility
     */
    public function injectImageUtility(ImageUtility $imageUtility)
    {
        $this->imageUtility = $imageUtility;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('file', 'object', 'a file or file reference');
    }

    /**
     * Returns the cropVariants array
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return array
     */
    public function render()
    {
        if (is_null($this->arguments['file'])) {
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must specify a File object.', 1522176433);
        }

        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $cropString = '';

        if ($file->hasProperty('crop') && $file->getProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantCollection = CropVariantCollection::create((string)$cropString)->asArray();
        return $cropVariantCollection;
    }
}
