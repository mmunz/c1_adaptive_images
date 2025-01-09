<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Utility;

use C1\AdaptiveImages\Service\SettingsService;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Exception;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageUtility
{
    private ?array $options;

    private array $settings;

    private FileInterface $originalFile;

    private array $cropVariants = [];

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly ImageService $imageService,
        private readonly CropVariantUtility $cropVariantUtility,
        private readonly DebugUtility $debugUtility,
        private readonly MathUtility $mathUtility,
        ?array $options = null,
        ?array $settings = null
    ) {

        if ($options) {
            // @extensionScannerIgnoreLine
            $this->setOptions($options);
            $this->cropVariants = $this->options['cropVariants'] ?? [];
        }

        if ($settings) {
            $this->settings = $settings;
        } else {
            $this->settings = $this->settingsService->getSettings();
        }
        if (!array_key_exists('default', $this->cropVariants)) {
            $this->cropVariants['default']['srcsetWidths'] = $this->settings['srcsetWidths'] ?? '320,600,992,1280,1920';
        }
    }

    public function init(?array $options = null): void
    {
        if ($options) {
            // @extensionScannerIgnoreLine
            $this->setOptions($options);
        }
        $this->cropVariants = $this->options['cropVariants'] ?? [];
    }

    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    public function setOriginalFile(FileInterface $file): void
    {
        $this->originalFile = $file;
    }

    public function processImage(array $processingConfiguration): array
    {
        /** @var FileReference $processedImage */
        $processedImage = $this->imageService->applyProcessingInstructions(
            $this->originalFile,
            $processingConfiguration
        );

        $ratio = $this->mathUtility->calculateRatio(
            $processedImage->getProperty('height'),
            $processedImage->getProperty('width')
        );
        if ($this->options && $this->options['debug'] && $this->options['debug'] === true) {
            if (!isset($processingConfiguration['additionalParameters'])) {
                $processingConfiguration['additionalParameters'] = '';
            }
            $processingConfiguration['additionalParameters'] .= $this->debugUtility->getDebugAnnotation(
                $processedImage->getProperty('width'),
                $processedImage->getProperty('height'),
                $ratio
            );
            $processedImage = $this->imageService->applyProcessingInstructions(
                $this->originalFile,
                $processingConfiguration
            );
        }

        $url = $this->imageService->getImageUri($processedImage);

        return [
            'url' => $url,
            'width' => $processedImage->getProperty('width'),
            'height' => $processedImage->getProperty('height'),
            'ratio' => $ratio
        ];
    }

    /**
     * Renders a source tag (set of srcset candidates for one cropVariant)
     * @throws Exception
     */
    public function processSrcsetImages(string $key, array $cropVariantConfig): array
    {
        $srcset = [];
        $srcWidths = explode(',', (string)$cropVariantConfig['srcsetWidths']);
        $maxWidthReached = false;

        $this->cropVariantUtility->setCropVariantCollection($this->originalFile);

        $defaultProcessConfiguration = [
            'width' => $this->options['width'] ?? null,
            'height' => $this->options['height'] ?? null,
            'crop' => $this->cropVariantUtility->getCropAreaForVariant($key)
        ];

        foreach ($srcWidths as $width) {
            $localProcessingConfiguration = $defaultProcessConfiguration;

            if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_allowUpscaling']) {
                $originalFileWidth = $this->originalFile->getProperty('width');
                if ($width >= $originalFileWidth) {
                    if ($maxWidthReached === true) {
                        continue;
                    } else {
                        // create one last srcset candidate with the width of the original image
                        $maxWidthReached = true;
                        $width = $originalFileWidth;
                    }
                }
            }

            $localProcessingConfiguration['width'] = $width . 'c';
            if (
                is_array($this->options)
                && array_key_exists('image_format', $this->options)
                && isset($cropVariantConfig['image_format'])
                && $cropVariantConfig['image_format'] > 0
            ) {
                $img_format = $this->options['image_format'];
                $localProcessingConfiguration['height'] = round(intval($width) / $img_format) . 'c';
            }

            $processedImage = $this->processImage($localProcessingConfiguration);

            $srcset[$width] = $processedImage;
        }

        return $srcset;
    }

    /**
     * returns a formatted srcset string
     */
    public function getSrcSetString(array $candidates): string
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
     */
    public function getRatioFromFirstCandidate(array $candidates): float
    {
        return reset($candidates)['ratio'];
    }

    /**
     * @throws Exception
     */
    public function getCropVariants(): array
    {
        foreach ($this->cropVariants as $key => $cropVariantConfig) {
            $candidates = $this->processSrcsetImages($key, $cropVariantConfig);
            $this->cropVariants[$key]['candidates'] = $candidates;
            $this->cropVariants[$key]['srcset'] = $this->getSrcSetString($candidates);
            $this->cropVariants[$key]['ratio'] = $this->getRatioFromFirstCandidate($candidates);
            // update srcsetWidths with actually generated candidate widths. Some of the configured sizes might
            // have been skipped for smaller images or when maxWidth for the image was reached.
            $this->cropVariants[$key]['srcsetWidths'] = implode(',', array_keys($candidates));
        }
        return $this->cropVariants;
    }
}
