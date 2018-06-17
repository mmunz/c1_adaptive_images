<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;
use TYPO3\CMS\Core\Resource\FileReference;

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
class GetSrcsetViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /** var array $widths */
    protected $widths = [];

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     * @inject
     */
    protected $imageService;

    /**
     * @var \C1\AdaptiveImages\Utility\ImageUtility
     * @inject
     */
    protected $imageUtility;

    /**
     * @var \C1\AdaptiveImages\Utility\MathUtility
     * @inject
     */
    protected $mathUtility;

    /**
     * @var \C1\AdaptiveImages\Utility\DebugUtility
     * @inject
     */
    protected $debugUtility;

    /**
     * @param $widths
     */
    public function setWidths($widths)
    {
        if (!is_array($this->arguments['widths'])) {
            $this->widths = explode(',', $widths);
        } else {
            $this->widths = $widths;
        }
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('file', 'object', 'a file or file reference', true);
        $this->registerArgument(
            'widths',
            'string',
            'String or array of integers describing the widths of candidates for srcset to be created',
            false,
            [320,640,1024,1440,1920]
        );
        $this->registerArgument(
            'cropVariant',
            'string',
            'select a cropping variant, in case multiple croppings have been specified or stored in FileReference',
            false,
            'default'
        );
        $this->registerArgument('debug', 'bool', 'Use IM/GM to write image infos on the srcset candidates');
    }

    /**
     * Returns the cropVariants array
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string
     */
    public function render()
    {

        $srcset = [];
        $this->setWidths($this->arguments['widths']);

        if (is_null($this->arguments['file'])) {
            throw new Exception('You must specify a File object.', 1522176433);
        }

        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $cropString = '';

        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingConfiguration = [
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file),
        ];

        foreach ($this->widths as $width) {
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
                "%s %dw",
                $imageUri,
                $width
            );
        };

        return implode(",", $srcset);
    }
}
