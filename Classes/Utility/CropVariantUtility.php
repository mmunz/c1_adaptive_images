<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Class CropVariantUtility
 */
class CropVariantUtility
{

    /**
     * @var CropVariantCollection $cropVariantCollection
     */
    protected $cropVariantCollection;

    /**
     * @var FileReference $file
     */
    protected $file;

    /**
     * Create a CropVariantCollection from file reference.
     *
     * @param FileInterface $file
     */
    public function setCropVariantCollection($file)
    {
        $cropString = '';
        $this->file = $file;
        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }
        $this->cropVariantCollection = CropVariantCollection::create((string)$cropString);
    }

    /**
     *
     * Returns a calculated Area with coordinates for cropping the actual image
     *
     * @param string $key
     * @return null|\TYPO3\CMS\Core\Imaging\ImageManipulation\Area
     */
    public function getCropAreaForVariant($key)
    {
        $cropArea = $this->cropVariantCollection
                ->getCropArea($key) ?? $this->cropVariantCollection->getCropArea('default');
        return $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($this->file);
    }
}
