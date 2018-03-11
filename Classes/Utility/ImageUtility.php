<?php

namespace C1\ImageRenderer\Utility;

use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ImageUtility
{

    /** @var array $options */
    protected $options;

    /** @var array $settings */
    protected $settings;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $originalFile;

    /**
     * @var array $cropVariants
     */
    protected $cropVariants;

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setOriginalFile($file)
    {
        $this->originalFile = $file;
    }

    public function __construct($options = null, $settings = null, $objectManager = null)
    {
        if (!$objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        } else {
            $this->objectManager = $objectManager;
        }

        if ($options) {
            $this->options = $options;
        }

        if ($settings) {
            $this->settings = $settings;
        } else {
            $pluginSettingsService = $this->objectManager->get('C1\\ImageRenderer\\Service\\SettingsService');
            $this->settings = $pluginSettingsService->getSettings();
        };
    }

    /**
     * Return an instance of ImageService
     *
     * @return ImageService
     */
    protected function getImageService()
    {
        return $this->objectManager->get(ImageService::class);
    }

    /**
     * returns a string with debug information for additionalParameters of a processing configuration
     * @param int $height
     * @param int $width
     * @param int|float $ratio
     * @return string
     */
    public function getDebugAnnotation($width, $height, $ratio)
    {
        $processor = $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'];

        if ($processor === 'GraphicsMagick') {
            return sprintf(
                '-pointsize 30 -gravity center -fill white -draw "text 10,20 \'%s x %s (%s)\'"',
                $width,
                $height,
                $ratio
            );
        }

        if ($processor === 'ImageMagick') {
            $text = sprintf(
                '-pointsize 30 -gravity Center -fill black -annotate +0+0 "%s x %s (%s)" -gravity NorthWest  -blur 2x5 ',
                $width,
                $height,
                $ratio
            );

            $text .= sprintf(
                '-pointsize 30 -gravity Center -fill white -annotate +1+1 "%s x %s (%s)" -gravity NorthWest',
                $width,
                $height,
                $ratio
            );
            return $text;
        };

        return '';
    }

    /**
     *
     * Calculates the ratio of an image.
     *
     * Returns a float which is the percentage of height compared to the width
     * Rounded to 2 decimals by default.
     *
     * @param int|float $height ;
     * @param int|float $width ;
     * @param int $precision;
     * @return float
     */
    public function calculateRatio($height, $width, $precision = 2)
    {
        return round($height / $width * 100, $precision);
    }

    /**
     * @param string $cropVariantKey
     * @return mixed
     */
    public function getCropAreaForVariant($cropVariantKey) {

        $cropString = $this->originalFile instanceof FileReference ? $this->originalFile->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea = $cropVariantCollection->getCropArea($cropVariantKey);

        if ($cropArea->isEmpty()) {
            if ($cropVariantKey !== 'default') {
                return null;
            }
            // try 'default' cropArea as fallback if the current variant has no cropArea
            $cropAreaDefault = $cropVariantCollection->getCropArea('default');
            if ($cropAreaDefault->isEmpty()) {
                return null;
            }
            return $cropAreaDefault->makeAbsoluteBasedOnFile($this->originalFile);
        } else {
            return $cropArea->makeAbsoluteBasedOnFile($this->originalFile);
        }
    }

    /**
     * @param array $processingConfiguration
     * @return array
     */

    public function processImage($processingConfiguration) {
        $imageService = $this->getImageService();
        $processedImage = $imageService->applyProcessingInstructions(
            $this->originalFile,
            $processingConfiguration
        );

        $ratio = $this->calculateRatio(
            $processedImage->getProperty('height'),
            $processedImage->getProperty('width')
        );

        //            if ($this->debugImgProperties) {
        $processingConfiguration['additionalParameters'] .= $this->getDebugAnnotation(
            $processedImage->getProperty('width'),
            $processedImage->getProperty('height'),
            $ratio

        );

        $processedImage = $imageService->applyProcessingInstructions(
            $this->originalFile,
            $processingConfiguration
        );

        $url = $imageService->getImageUri($processedImage);
        return [
            url => $url,
            width => $processedImage->getProperty('width'),
            height => $processedImage->getProperty('height'),
            ratio => $ratio
        ];
    }

    /**
     * Renders a source tag (set of srcset candidates for one cropVariant)
     * @param string $cropVariantKey
     * @param array $cropVariantConfig
     * @return array
     */
    protected function processSrcsetImages($cropVariantKey, $cropVariantConfig)
    {
        $srcset = array();
        $ratio = '';
        $srcWidths = explode(',', $cropVariantConfig['srcsetWidths']);
        $maxWidthReached = false;

        $defaultProcessConfiguration = [
            'width' => $this->options['width'],
            'height' => $this->options['height'],
            'crop' => $this->getCropAreaForVariant($cropVariantKey)
        ];

        foreach ($srcWidths as $width) {
            $localProcessingConfiguration = $defaultProcessConfiguration;

            if ($localProcessingConfiguration['width'] > 0 && $width > $localProcessingConfiguration['width']) {
                if ($maxWidthReached === true) {
                    continue;
                } else {
                    // create one last srcset candidate with the width from the fluid template/be settings
                    $maxWidthReached = true;
                    $width = $localProcessingConfiguration['width'];
                }
            }

            $localProcessingConfiguration['crop'] = $this->getCropAreaForVariant($cropVariantKey);

            if ($cropVariantConfig['image_format'] > 0) {
                $img_format = $this->additionalConfig['image_format'];
                $localProcessingConfiguration['width'] = $width . "c";
                $localProcessingConfiguration['height'] = round(intval($width) / $img_format) . "c";
            } else {
                $localProcessingConfiguration['width'] = $width . 'm';
            }

            $processedImage = $this->processImage($localProcessingConfiguration);

            $srcset[$width] = $processedImage;



        };

        return $srcset;

//        $this->cssMediaQueries[] = array(
//            'media' => $cropVariantConfig['media'],
//            'ratio' => $ratio,
//            'class' => $this->getMediaQueryClassname($cropVariantConfig['media'], $ratio),
//        );
//
//        $tagBuilder = $this->getTagBuilder();
//        $tagBuilder->setTagName('source');
//        $tagBuilder->addAttribute('media', $cropVariantConfig['media']);
//        $tagBuilder->addAttribute('srcset', implode(',', $srcset));
//        $tagBuilder->addAttribute('sizes', '100vw');
//        $tag = $tagBuilder->render();
//        return $tag;
    }

    /**
     * returns a formatted srcset string
     *
     * @param array $candidates
     * @return string
     */
    public function getSrcSetString($candidates) {
        $srcset = [];
        foreach ($candidates as $candidate) {
            $srcset[] = sprintf('%s %dw', $candidate['url'], $candidate['width']);
        }
        return implode(',', $srcset);
    }

    /*
     * Get the ratio for a given cropVariant
     *
     * Because all candidates have the same ratio we can just return the 'ratio' from the first child of the candidates
     * array.
     *
     * @param $candidates
     */
    public function getRatioFromFirstCandidate($candidates) {
        return reset($candidates)['ratio'];
    }


    /**
     *
     */
    public function getCropVariants()
    {
        $this->cropVariants = $this->options['additionalConfig']['sources'] ?? [];

        if (!array_key_exists('default', $this->cropVariants)) {
            $this->cropVariants['default']['srcsetWidths'] = $this->settings->srcsetWidths ?? '320,600,992,1280,1920';
        }

        foreach ($this->cropVariants as $cropVariantKey => $cropVariantConfig) {
            $candidates = $this->processSrcsetImages($cropVariantKey, $cropVariantConfig);
            $this->cropVariants[$cropVariantKey]['candidates'] = $candidates;
            $this->cropVariants[$cropVariantKey]['srcset'] = $this->getSrcsetString($this->cropVariants[$cropVariantKey]['candidates']);
            $this->cropVariants[$cropVariantKey]['ratio'] = $this->getRatioFromFirstCandidate($candidates);
        }

        return $this->cropVariants;
    }


    public function getDefaultImage()
    {
        $processingConfiguration = [
            'width' => $this->options['width'],
            'height' => $this->options['height'],
            'crop' => $this->getCropAreaForVariant('default')
        ];

        $processedImage = $this->processImage($processingConfiguration);

        return $processedImage;
    }


}