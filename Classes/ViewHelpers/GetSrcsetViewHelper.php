<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\DebugUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Create a srcset string from given widths
 *
 * = Examples =
 * @Todo
 * <code title="Default">
 * <ai:image.placeholder file="EXT:myext/Resources/Public/typo3_logo.png" width="50px" base64="1" />
 * </code>
 * <output>
 * data:image/svg+xml;base64,... base64 encoded image ...
 * </output>
 *
 */
class GetSrcsetViewHelper extends AbstractViewHelper
{
    /**
     * No need to analyse the doc comment above the render method.
     * This also caused failed tests when testing TYPO3 8.7
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    protected function registerRenderMethodArguments()
    {
        return;
    }

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @param ImageService $imageService
     */
    public function injectImageService(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

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
     * @var \C1\AdaptiveImages\Utility\DebugUtility
     */
    protected $debugUtility;

    /**
     * @param DebugUtility $debugUtility
     */
    public function injectDebugUtility(DebugUtility $debugUtility)
    {
        $this->debugUtility = $debugUtility;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', FileInterface::class, 'a file or file reference', true);
        $this->registerArgument(
            'widths',
            'string',
            'String or array of integers describing the widths of candidates for srcset to be created',
            false,
            [320, 640, 1024, 1440, 1920]
        );
        $this->registerArgument(
            'cropVariant',
            'string',
            'select a cropping variant, in case multiple croppings have been specified or stored in FileReference',
            false,
            'default'
        );
        $this->registerArgument('debug', 'bool', 'Use IM/GM to write image infos on the srcset candidates', false, false);
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
    }

    /**
    * @return string
    */
    public function render()
    {
        $srcset = [];

        $widths = $this->arguments['widths'];
        if (!is_array($widths)) {
            $widths = explode(',', (string) $widths);
        }

        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $cropString = '';
        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropVariant = $this->arguments['cropVariant'];
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingConfiguration = [
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file),
        ];

        foreach ($widths as $width) {
            $processingConfiguration['width'] = $width . 'm';

            /** @var FileReference $processedImage */
            $processedImage = $this->imageService->applyProcessingInstructions($file, $processingConfiguration);

            if ($this->arguments['debug'] === true) {
                $processingConfiguration['additionalParameters'] = $this->debugUtility->getDebugAnnotation(
                    $processedImage->getProperty('width'),
                    $processedImage->getProperty('height'),
                    $this->mathUtility->calculateRatio(
                        $processedImage->getProperty('height'),
                        $processedImage->getProperty('width')
                    )
                );
                $processedImage = $this->imageService->applyProcessingInstructions($file, $processingConfiguration);
            }

            /** @var string $imageUri */
            $imageUri = $this->imageService->getImageUri($processedImage, $this->arguments['absolute']);

            $srcset[] = sprintf(
                '%s %dw',
                $imageUri,
                $width
            );
        }
        return implode(',', $srcset);
    }
}
