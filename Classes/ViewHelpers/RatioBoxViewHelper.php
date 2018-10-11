<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 *
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
class RatioBoxViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

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
     * @var \C1\AdaptiveImages\Utility\RatioBoxUtility
     * @inject
     */
    protected $ratioBoxUtility;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('file', 'object', 'a file or file reference', true);
        $this->registerArgument(
            'cropVariant',
            'string',
            'select a cropping variant, in case multiple croppings have been specified or stored in FileReference',
            false,
            'default'
        );
        $this->registerArgument(
            'mediaQueries',
            'array',
            'media queries to use for the different cropVariants',
            false
        );
    }

    /**
     * Get crop property from file reference
     * @param FileReference $file
     *
     * @return array
     */
    public function cropVariantsFromFile($file)
    {
        $crop = $file->getProperty('crop');
        if (!$crop) {
            return false;
        }
        return json_decode($crop);
    }

    /**
     * Returns the cropVariants array
     *
     * @param string $content
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string
     */
    public function render()
    {
        if (is_null($this->arguments['file'])) {
            throw new Exception('You must specify a File object.', 1522176433);
        }

        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        $mediaQueries = $this->arguments['mediaQueries'];

        $cropString = '';

        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }

        $cropVariantCollection = CropVariantCollection::create((string)$cropString);

        $cropVariants = [];

        if (array_key_exists('default', $mediaQueries) === false) {
            $mediaQueries['default'] = '';
        }

        //DebuggerUtility::var_dump($mediaQueries);

        foreach ($mediaQueries as $key => $mediaQuery) {
            $cropVariant = $key;
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);

            if (!$cropArea) {
                continue;
            }

            $crop = $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file)->asArray();

            if ($crop) {
                $width = $crop['width'];
                $height = $crop['height'];
            } else {
                $width = $file->getProperty('width');
                $height = $file->getProperty('height');
            }

            $ratio = $this->mathUtility->calculateRatio($height, $width, 2);

            $cropVariants[] = [
                'ratio' => $ratio,
                'media' => $mediaQuery
            ];
        }

        $this->ratioBoxUtility->setRatioBoxBase('rb');
        $classNames = $this->ratioBoxUtility->getRatioBoxClassNames($cropVariants);

        $this->tag->setTagName('div');
        $this->tag->setContent($this->renderChildren());
        $this->tag->addAttribute('class', implode(' ', $classNames));

        return $this->tag->render();
    }
}
