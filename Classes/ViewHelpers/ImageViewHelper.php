<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Create a adaptive image tag
 *
 * = Examples =
 * @Todo
 * <code title="Default">
 * <ai:image file="EXT:myext/Resources/Public/typo3_logo.png" />
 * </code>
 * <output>
 *
 * </output>
 *
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

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
     * @var \C1\AdaptiveImages\Utility\RatioBoxUtility
     * @inject
     */
    protected $ratioBoxUtility;

    /** @var \C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility
     * @inject
     */
    protected $imagePlaceholderUtility;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'sources',
            'array',
            'Array describing the candidates for srcset to be created',
            false
        );
        $this->registerArgument(
            'lazy',
            'bool',
            'lazy load images with lazyload.js',
            false,
            true
        );
        $this->registerArgument(
            'debug',
            'bool',
            'Use IM/GM to write image infos on the srcset candidates',
            false,
            false
        );
        $this->registerArgument(
            'srcsetWidths',
            'string',
            'comma seperated list of integers containing the widths of srcset candidates to create',
            false,
            '360,768,1024,1920'
        );
        $this->registerArgument(
            'placeholderInline',
            'boolean',
            'Include placeholder inline in HTML (base64 encoded)',
            false,
            true
        );
        $this->registerArgument('placeholderWidth', 'integer', 'Width of the placeholder image', false, 100);
        $this->registerArgument(
            'ratiobox',
            'bool',
            'The image is wrapped in a ratio box if true.',
            false,
            false
        );
    }

    /**
     * Sets the tag name to $this->tagName.
     * Additionally, sets all tag attributes which were registered in
     * $this->tagAttributes and additionalArguments.
     *
     * Will be invoked just before the render method.
     *
     * @api
     */
    public function initialize()
    {
        parent::initialize();

        $this->imageUtility->setOriginalFile($this->arguments['image']);
        $this->imageUtility->init(
            [
                'debug' => $this->arguments['debug'],
                'cropVariants' => [
                    'default' => [
                        'srcsetWidths' => $this->arguments['srcsetWidths']
                    ]
                ]
            ]
        );

        $this->addAdditionalAttributes();
        $this->addDataAttributes();
    }

    /**
     * getPlaceHolder
     * @return string
     */
    public function getPlaceholder()
    {
        $placeholder = $this->imagePlaceholderUtility->getPlaceholderImage(
            $this->arguments['image'],
            $this->arguments['placeholderInline'],
            $this->arguments['cropVariant'],
            $this->arguments['placeholderWidth']
        );
        return $placeholder . ' ' . $this->arguments['placeholderWidth'] . 'w';
    }

    /** isLazyLoading
     * @return bool
     */
    public function isLazyLoading()
    {
        if ($this->hasArgument('lazy')) {
            return $this->arguments['lazy'] === true;
        } else {
            return true;
        }
    }

    /**
     * addAdditionalAttributes
     *
     * merge our own additionalAttributes with the ones coming from the viewhelper arguments (if any). The latter takes
     * precedence, i.e.: It is possible to overwrite any default param from the viewHelper if necessary.
     */
    public function addAdditionalAttributes()
    {
        $additionalAttributes = null;

        $extraAdditionalAttributes = [
            'sizes' => '100vw',
        ];

        if ($this->isLazyLoading()) {
            $extraAdditionalAttributes['srcset'] = $this->getPlaceholder();
        } else {
            $extraAdditionalAttributes['srcset'] = $this->getSrcSetString();
        }

        if ($this->hasArgument('additionalAttributes') && is_array($this->arguments['additionalAttributes'])) {
            $additionalAttributes = array_merge($extraAdditionalAttributes, $this->arguments['additionalAttributes']);
            $this->tag->addAttributes($additionalAttributes);
        } else {
            $additionalAttributes = $extraAdditionalAttributes;
            $this->tag->addAttributes($additionalAttributes);
        }
        $this->arguments['additionalAttributes'] = $additionalAttributes;
    }

    /**
     * addDataAttributes
     *
     * merge our own data-attributes with the ones coming from the viewhelper arguments (if any). The latter takes
     * precedence, i.e.: It is possible to overwrite any default data-attributes used here from the viewHelper's
     * attributes if necessary.
     */
    public function addDataAttributes()
    {
        $data = [];

        if ($this->isLazyLoading()) {
            $data['sizes'] = 'auto';
            $data['srcset'] = $this->getSrcSetString();
        }

        if ($this->hasArgument('data') && is_array($this->arguments['data'])) {
            $data = array_merge($data, $this->arguments['data']);
        }
        $this->arguments['data'] = $data;
        foreach ($data as $dataAttributeKey => $dataAttributeValue) {
            $this->tag->addAttribute('data-' . $dataAttributeKey, $dataAttributeValue);
        }
    }

    public function wrapInRatioBox($content)
    {

        /* @ToDo refactor. same code as in ratioboxviewhelper */

        /** @var FileInterface $file */
        $file = $this->arguments['image'];

        /** @var TagBuilder */
        $tagBuilder = new TagBuilder('div', $content);

        $mediaQueries = [];
        $cropString = '';

        if ($file->hasProperty('crop')) {
            $cropString = $file->getProperty('crop');
        }
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);

        $cropVariants = [];

        if (array_key_exists($this->arguments['cropVariant'], $mediaQueries) === false) {
            $mediaQueries['default'] = '';
        }

        $cropVariant = $this->arguments['cropVariant'];
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);

//        if (!$cropArea) {
//            continue;
//        }

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
            'media' => ''
        ];

        $this->ratioBoxUtility->setRatioBoxBase('rb');
        $classNames = $this->ratioBoxUtility->getRatioBoxClassNames($cropVariants);

        $tagBuilder->setTagName('div');
        $tagBuilder->setContent($content);
        $tagBuilder->addAttribute('class', implode(' ', $classNames));
        return $tagBuilder->render();
    }

    /** getSrcSetString
     * @return string
     */
    public function getSrcSetString()
    {
        $cropVariants = $this->imageUtility->getCropVariants();
        return $this->imageUtility->getSrcSetString($cropVariants[$this->arguments['cropVariant']]['candidates']);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws Exception
     */
    public function render()
    {
        $image = parent::render();

        if ($this->arguments['ratiobox'] === true) {
            return $this->wrapInRatioBox($image);
        } else {
            return $image;
        }
    }
}
