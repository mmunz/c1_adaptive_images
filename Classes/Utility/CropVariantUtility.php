<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;

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
     * @var FileInterface $file
     */
    protected $file;

    /**
     * @var \C1\AdaptiveImages\Utility\MathUtility
     */
    protected $mathUtility;

    public function __construct(MathUtility $mathUtility)
    {
        $this->mathUtility = $mathUtility;
    }

    /**
     * Create a CropVariantCollection from file reference.
     *
     * @param FileInterface $file
     * @return void
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
     * Get property cropVariantCollection
     * @return CropVariantCollection
     */
    public function getCropVariantCollection()
    {
        return $this->cropVariantCollection;
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
     * @param array $aspectRatios
     * @return array
     */
    public function getCropVariants(array $mediaQueries, array $aspectRatios = [])
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

            if (array_key_exists($key, $aspectRatios) && $aspectRatios[$key] > 0
            ) {
                $height = $width / $aspectRatios[$key];
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
