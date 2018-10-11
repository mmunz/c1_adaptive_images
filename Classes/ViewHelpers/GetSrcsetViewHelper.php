<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\DebugUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
    use CompileWithRenderStatic;

    /**
     * @var bool Reset singletons created by subject
     */
    protected $resetSingletonInstances = true;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var \C1\AdaptiveImages\Utility\ImageUtility
     * @inject
     */
    protected $imageUtility;

    /** @var ImageService */
    protected static $imageService;

    /**
     * @param \TYPO3\CMS\Extbase\Service\ImageService $imageService
     */
    public static function injectImageService(\TYPO3\CMS\Extbase\Service\ImageService $imageService)
    {
        self::$imageService = $imageService;
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
            [320, 640, 1024, 1440, 1920]
        );
        $this->registerArgument(
            'cropVariant',
            'string',
            'select a cropping variant, in case multiple croppings have been specified or stored in FileReference',
            false,
            'default'
        );
        $this->registerArgument('debug', 'bool', 'Use IM/GM to write image infos on the srcset candidates');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
    }

    /**
    * @param array $arguments
    * @param \Closure $renderChildrenClosure
    * @param RenderingContextInterface $renderingContext
    * @return string
    * @throws Exception
    */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $srcset = [];
        $widths = $arguments['widths'] ?? '';

        if (!is_array($widths)) {
            $widths = explode(',', $widths);
        }

        /** @var FileInterface $file */
        $file = $arguments['file'];

//        if (! $file instanceof FileInterface) {
//            throw new Exception('You must specify a File object.', 1522176433);
//        }

        $cropString = '';
        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropVariant = $arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingConfiguration = [
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file),
        ];

        foreach ($widths as $width) {
            $processingConfiguration['width'] = $width . 'm';

            $imageService = self::getImageService();

            /** @var FileReference $processedImage */
            $processedImage = $imageService->applyProcessingInstructions($file, $processingConfiguration);

            if ($arguments['debug'] === true) {
                $mathUtility = self::getMathUtility();
                $debugUtility = self::getDebugUtility();
                $processingConfiguration['additionalParameters'] = $debugUtility->getDebugAnnotation(
                    $processedImage->getProperty('width'),
                    $processedImage->getProperty('height'),
                    $mathUtility->calculateRatio(
                        $processedImage->getProperty('height'),
                        $processedImage->getProperty('width')
                    )
                );
                $processedImage = $imageService->applyProcessingInstructions($file, $processingConfiguration);
            }

            /** @var string $imageUri */
            $imageUri = $imageService->getImageUri($processedImage, $arguments['absolute']);

            $srcset[] = sprintf(
                '%s %dw',
                $imageUri,
                $width
            );
        }

        return implode(',', $srcset);
    }

    /**
     * Return an instance of ImageService using object manager
     *
     * @return ImageService
     */
    protected static function getImageService()
    {
        return self::$imageService;
    }

    /**
     * Return an instance of mathUtility using object manager
     *
     * @return MathUtility
     */
    protected static function getMathUtility()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(MathUtility::class);
    }

    /**
     * Return an instance of getDebugUtility using object manager
     *
     * @return DebugUtility
     */
    protected static function getDebugUtility()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(DebugUtility::class);
    }
}
