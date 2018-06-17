<?php

declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Class ImageUtility
 * @package C1\AdaptiveImages\Utility
 */
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
    protected $cropVariants = [];

    /**
     * @var CropVariantCollection $cropVariantCollection
     */
    protected $cropVariantCollection;


    /**
     * @param array $options
     */
    public function init($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        };
        $this->cropVariants = $this->options['cropVariants'] ?? [];
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     Create a CropVariantCollection from file reference.
     */
    public function setCropVariantCollection()
    {
        $cropString = $this->originalFile instanceof FileReference ? $this->originalFile->getProperty('crop') : '';
        $this->cropVariantCollection = CropVariantCollection::create((string)$cropString);
    }

    /**
     * @param FileInterface $file
     */
    public function setOriginalFile($file)
    {
        $this->originalFile = $file;
        $this->setCropVariantCollection();
    }

    /**
     * ImageUtility constructor.
     * @param null|array $options
     * @param null|array $settings
     * @param null|ObjectManager $objectManager
     */
    public function __construct($options = null, $settings = null, $objectManager = null)
    {
        if (!$objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        } else {
            $this->objectManager = $objectManager;
        }

        if ($options) {
            $this->setOptions($options);
            $this->cropVariants = $this->options['cropVariants'] ?? [];
        }

        if ($settings) {
            $this->settings = $settings;
        } else {
            $pluginSettingsService = $this->objectManager->get('C1\\AdaptiveImages\\Service\\SettingsService');
            $this->settings = $pluginSettingsService->getSettings();
        };

        if (!array_key_exists('default', $this->cropVariants)) {
            $this->cropVariants['default']['srcsetWidths'] = $this->settings->srcsetWidths ?? '320,600,992,1280,1920';
        }
    }

    /**
     * Return an instance of ImageService
     *
     * @return object
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
     * @param string $processor
     * @return string
     */
    public function getDebugAnnotation($width, $height, $ratio, $processor = null)
    {
        if (!$processor) {
            $processor = $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'];
        }

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
                '-pointsize 30 -gravity Center \
                        -fill black -annotate +0+0 "%s x %s (%s)" -gravity NorthWest ',
                $width,
                $height,
                $ratio
            );

            $text .= sprintf(
                '-pointsize 30 -gravity Center -fill white -annotate +2+2 "%s x %s (%s)" -gravity NorthWest',
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
     *
     * Returns a calculated Area with coordinates for croppting the actual image
     *
     * @param string $key
     * @return null|Area
     */
    public function getCropAreaForVariant($key)
    {
        $cropArea = $this->cropVariantCollection
                ->getCropArea($key) ?? $this->cropVariantCollection->getCropArea('default');
        return $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($this->originalFile);
    }

    /**
     * @param array $processingConfiguration
     * @return array
     */

    public function processImage($processingConfiguration)
    {
        $imageService = $this->getImageService();
        /** @var FileReference $processedImage */
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
            'url' => $url,
            'width' => $processedImage->getProperty('width'),
            'height' => $processedImage->getProperty('height'),
            'ratio' => $ratio
        ];
    }

    /**
     * Renders a source tag (set of srcset candidates for one cropVariant)
     * @param string $key
     * @param array $cropVariantConfig
     * @return array
     */
    public function processSrcsetImages($key, $cropVariantConfig)
    {
        $srcset = [];
        $srcWidths = explode(',', $cropVariantConfig['srcsetWidths']);
        $maxWidthReached = false;

        $defaultProcessConfiguration = [
            'width' => $this->options['width'],
            'height' => $this->options['height'],
            'crop' => $this->getCropAreaForVariant($key)
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

            if ($cropVariantConfig['image_format'] > 0) {
                $img_format = $this->options['image_format'];
                $localProcessingConfiguration['width'] = $width . "c";
                $localProcessingConfiguration['height'] = round(intval($width) / $img_format) . "c";
            } else {
                $localProcessingConfiguration['width'] = $width . 'm';
            }

            $processedImage = $this->processImage($localProcessingConfiguration);

            $srcset[$width] = $processedImage;
        };

        return $srcset;
    }

    /**
     * returns a formatted srcset string
     *
     * @param array $candidates
     * @return string
     */
    public function getSrcSetString($candidates)
    {
        $srcset = [];
        foreach ($candidates as $candidate) {
            $srcset[] = sprintf('%s %dw', $candidate['url'], $candidate['width']);
        }
        return implode(',', $srcset);
    }

    /**
     * Get the ratio for a given cropVariant
     *
     * Because all candidates have the same ratio we can just return the 'ratio' from the first child of the candidates
     * array.
     *
     * @param $candidates
     * @return array
     */
    public function getRatioFromFirstCandidate($candidates)
    {
        return reset($candidates)['ratio'];
    }

//    public function getSvgPlaceholderFromFirstCandidate($candidates)
//    {
//        return $this->getSvgPlaceholder(reset($candidates)['width'], reset($candidates)['height'] ?? null);
//    }

    /**
     * Returns a space separated string of data attributes
     * @return string
     */
    public function formatDataAttributes()
    {
        $data = $this->options['data'];
        $tmpData = [];

        if ($data && is_array($data)) {
            foreach ($data as $dataAttributeKey => $dataAttributeValue) {
                $tmpData[] = "data-{$dataAttributeKey}=\"{$dataAttributeValue}\"";
            }
        }
        return implode(' ', $tmpData);
    }

    /**
     * Returns a space separated string of additionalAttributes
     * @return string
     */
    public function formatAdditionalAttributes()
    {
        $data = $this->options['additionalAttributes'];
        $tmpData = [];

        if ($data && is_array($data)) {
            foreach ($data as $dataAttributeKey => $dataAttributeValue) {
                $tmpData[] = sprintf('%s="%s"', $dataAttributeKey, $dataAttributeValue);
            }
        }
        return implode(' ', $tmpData);
    }

    /**
     * Get the default image
     * This can for example be used as fallback image if the browser supports no srcset/sources attributes
     *
     * @return array
     */
    public function getDefaultImage()
    {

        $processingConfiguration = [
            'width' => $this->options['width'],
            'height' => $this->options['height'],
            'crop' => $this->getCropAreaForVariant('default')
        ];

        $processedImage = $this->processImage($processingConfiguration);
        // @Todo: unset unneeded keys
        // $this->options['additionalAttributes']['srcset'] = $this->settings['placeholder']['dataImage'] . ' 1w';
        $this->options['data']['srcset'] = $this->cropVariants['default']['srcset'];
        $mergedWithOptions = array_merge_recursive($this->options, $processedImage);

        $mergedWithOptions['dataString'] = $this->formatDataAttributes();
        $mergedWithOptions['additionalAttributesString'] = $this->formatAdditionalAttributes();

        return $mergedWithOptions;
    }


    /**
     *
     */
    public function getCropVariants()
    {

        if (!array_key_exists('default', $this->cropVariants)) {
            $this->cropVariants['default']['srcsetWidths'] = $this->settings->srcsetWidths ?? '320,600,992,1280,1920';
        }

        foreach ($this->cropVariants as $key => $cropVariantConfig) {
            $candidates = $this->processSrcsetImages($key, $cropVariantConfig);
            $this->cropVariants[$key]['candidates'] = $candidates;
            $this->cropVariants[$key]['srcset'] = $this->getSrcSetString($candidates);
            $this->cropVariants[$key]['ratio'] = $this->getRatioFromFirstCandidate($candidates);
            //$this->cropVariants[$key]['svgPlaceholder'] = $this->getSvgPlaceholderFromFirstCandidate($candidates);
            // update srcsetWidths with actually generated candidate widths. Some of the configured sizes might
            // have been skipped for smaller images or when maxWidth for the image was reached.
            $this->cropVariants[$key]['srcsetWidths'] = implode(',', array_keys($candidates));
        }

        return $this->cropVariants;
    }
}
