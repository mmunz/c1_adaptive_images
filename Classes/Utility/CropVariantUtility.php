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
     * @var \C1\AdaptiveImages\Utility\MathUtility
     */
    protected $mathUtility;

    /**
     * @param MathUtility $mathUtility
     */
    public function injectMathUtility(MathUtility $mathUtility)
    {
        $this->mathUtility = $mathUtility;
    }

    /**
     * Create a CropVariantCollection from file reference.
     *
     * @param FileInterface $file
     */
    public function setCropVariantCollection(FileInterface $file)
    {
        $cropString = '';
        $this->file = $file;
        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }
        $this->cropVariantCollection = CropVariantCollection::create((string)$cropString);
    }

    /**
     * Returns a calculated Area with coordinates for cropping the actual image
     *
     * @param string $key
     * @return null|\TYPO3\CMS\Core\Imaging\ImageManipulation\Area
     */
    public function getCropAreaForVariant(string $key)
    {
        $cropArea = $this->cropVariantCollection
                ->getCropArea($key) ?? $this->cropVariantCollection->getCropArea('default');
        return $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($this->file);
    }

    /**
     * Returns a calculated array with coordinates for cropping the actual image
     *
     * @param string $key
     * @return null|array
     */
    public function getCropAreaForVariantAsArray(string $key)
    {
        $area = $this->getCropAreaForVariant($key);
        if (!is_null($area)) {
            return $area->asArray();
        }
        return null;
    }

    /**
     * Return a CropVariants array (beware: not related to TYPO3's CropVariants, needs a better naming - ToDo)
     * @param array $mediaQueries
     * @return array
     */
    public function getCropVariants(array $mediaQueries)
    {
        $cropVariants = [];

        foreach ($mediaQueries as $key => $mediaQuery) {
            $crop = $this->getCropAreaForVariantAsArray($key);

            if (is_array($crop)) {
                $width = $crop['width'];
                $height = $crop['height'];
            } else {
                $width = $this->file->getProperty('width');
                $height = $this->file->getProperty('height');
            }

            $ratio = $this->mathUtility->calculateRatio($height, $width, 2);

            $cropVariants[$key] = [
                'ratio' => $ratio,
                'media' => $mediaQuery
            ];
        }
        return $cropVariants;
    }
}
