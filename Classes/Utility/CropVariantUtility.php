<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;

class CropVariantUtility
{
    protected CropVariantCollection $cropVariantCollection;
    protected FileInterface $file;

    public function __construct(
        private readonly MathUtility $mathUtility
    ) {
    }

    /**
     * Create a CropVariantCollection from file reference.
     */
    public function setCropVariantCollection(FileInterface $file): void
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
     */
    public function getCropVariantCollection(): CropVariantCollection
    {
        return $this->cropVariantCollection;
    }

    /**
     * Returns a calculated Area with coordinates for cropping the actual image
     */
    public function getCropAreaForVariant(string $key): ?Area
    {
        $cropArea = $this->cropVariantCollection->getCropArea($key);
        return $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($this->file);
    }

    /**
     * Returns a calculated array with coordinates for cropping the actual image
     */
    public function getCropAreaForVariantAsArray(string $key): ?array
    {
        $area = $this->getCropAreaForVariant($key);
        if (!is_null($area)) {
            return $area->asArray();
        }
        return null;
    }

    /**
     * Return a CropVariants array (beware: not related to TYPO3's CropVariants, needs a better naming - ToDo)
     */
    public function getCropVariants(array $mediaQueries): array
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
