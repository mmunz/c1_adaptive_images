<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Class ImageUtility
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
     * @var \C1\AdaptiveImages\Utility\DebugUtility
     * @inject
     */
    protected $debugUtility;

    /**
     * @var \C1\AdaptiveImages\Utility\MathUtility
     * @inject
     */
    protected $mathUtility;

    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $originalFile;

    /**
     * @var array $cropVariants
     */
    protected $cropVariants = [];

    /**
     * @var \C1\AdaptiveImages\Utility\CropVariantUtility
     * @inject
     */
    protected $cropVariantUtility;

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
        }

        if (!array_key_exists('default', $this->cropVariants)) {
            $this->cropVariants['default']['srcsetWidths'] = $this->settings->srcsetWidths ?? '320,600,992,1280,1920';
        }
        $this->cropVariantUtility = $this->objectManager->get('C1\\AdaptiveImages\\Utility\\CropVariantUtility');
    }

    /**
     * @param array $options
     */
    public function init($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
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
     * @param FileInterface $file
     */
    public function setOriginalFile($file)
    {
        $this->originalFile = $file;
    }

    /**
     * Return an instance of ImageService
     *
     * @return \object|ImageService
     */
    protected function getImageService()
    {
        return $this->objectManager->get(ImageService::class);
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
        if ($this->options['debug'] && $this->options['debug'] === true) {
            $ratio = $this->mathUtility->calculateRatio(
                $processedImage->getProperty('height'),
                $processedImage->getProperty('width')
            );
            $processingConfiguration['additionalParameters'] .= $this->debugUtility->getDebugAnnotation(
                $processedImage->getProperty('width'),
                $processedImage->getProperty('height'),
                $ratio
            );
            $processedImage = $imageService->applyProcessingInstructions(
                $this->originalFile,
                $processingConfiguration
            );
        }

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

        $this->cropVariantUtility->setCropVariantCollection($this->originalFile);

        $defaultProcessConfiguration = [
            'width' => $this->options['width'],
            'height' => $this->options['height'],
            'crop' => $this->cropVariantUtility->getCropAreaForVariant($key)
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
                $localProcessingConfiguration['width'] = $width . 'c';
                $localProcessingConfiguration['height'] = round(intval($width) / $img_format) . 'c';
            } else {
                $localProcessingConfiguration['width'] = $width . 'm';
            }

            $processedImage = $this->processImage($localProcessingConfiguration);

            $srcset[$width] = $processedImage;
        }

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
