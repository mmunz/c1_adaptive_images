<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

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
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('file', FileInterface::class, 'a file or file reference', true);
        $this->registerArgument('asString', 'bool', 'return as string or array', false, false);
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
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must specify a File or FileReference object implementing FileInterface', 1522176433);
        }

        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $asString = false;
        if ($this->arguments['asString']) {
            $asString = true;
        }

        $cropString = '';

        if ($file->hasProperty('crop') && $file->getProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantString = CropVariantCollection::create((string)$cropString);
        if ($asString) {
            return $cropVariantString;
        } else {
            return $cropVariantString->asArray();
        }
    }
}
