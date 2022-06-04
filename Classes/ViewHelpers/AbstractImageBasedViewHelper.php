<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use C1\AdaptiveImages\Utility\RatioBoxUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

abstract class AbstractImageBasedViewHelper extends ImageViewHelper
{
    /** @var array $cropVariants */
    protected $cropVariants;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var ImageUtility
     */
    protected $imageUtility;

    /**
     * @var \C1\AdaptiveImages\Utility\RatioBoxUtility
     */
    protected $ratioBoxUtility;

    /** @var \C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility
     */
    protected $imagePlaceholderUtility;

    /**
     * @var string
     */
    protected $tagName = 'img';

    public function __construct(ImageUtility $imageUtility, RatioBoxUtility $ratioBoxUtility, ImagePlaceholderUtility $imagePlaceholderUtility, ImageService $imageService)
    {
        // not calling parent constructor on purpose because TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper sets the
        // imageService there with makeInstance which made the unit tests fail. Instead we just set imageService here.
        $this->imageUtility = $imageUtility;
        $this->ratioBoxUtility = $ratioBoxUtility;
        $this->imagePlaceholderUtility = $imagePlaceholderUtility;
        $this->imageService = $imageService;
        $this->setTagBuilder(new TagBuilder($this->tagName));
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'lazy',
            'bool',
            'lazy load images with lazyload.js',
            false,
            false
        );
        $this->registerArgument(
            'debug',
            'bool',
            'Use IM/GM to write image infos on the srcset candidates',
            false,
            false
        );
        $this->registerArgument(
            'jsdebug',
            'bool',
            'Add debug information about the current image using javascript.',
            false,
            false
        );
        $this->registerArgument(
            'srcsetWidths',
            'string',
            'comma seperated list of integers containing the widths of srcset candidates to create for the img tag',
            false,
            '360,768,1024,1920'
        );
        $this->registerArgument(
            'sizes',
            'string',
            'sizes attribute for the img tag. Takes precedence over additionalAttributes["sizes"] if both are given.',
            false,
            '100vw'
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

    /** isLazyLoading
     * @return bool
     */
    public function isLazyLoading()
    {
        if ($this->hasArgument('lazy')) {
            return $this->arguments['lazy'] === true;
        } else {
            return false;
        }
    }
    /**
     * getPlaceHolder
     * @param string $cropVariant
     * @return string
     */
    public function getPlaceholder(string $cropVariant)
    {
        $placeholder = $this->imagePlaceholderUtility->getPlaceholderImage(
            $this->arguments['image'],
            $this->arguments['placeholderInline'],
            $cropVariant,
            $this->arguments['placeholderWidth'],
            $this->arguments['absolute'] ?? false
        );
        return $placeholder . ' ' . $this->arguments['placeholderWidth'] . 'w';
    }

    /**
     * addDataAttributes
     *
     * merge our own data-attributes with the ones coming from the viewhelper arguments (if any). The latter takes
     * precedence, i.e.: It is possible to overwrite any default data-attributes used here from the viewHelper's
     * attributes if necessary.
     *
     * @return void
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
        if ($this->hasArgument('jsdebug')) {
            $data['img-debug'] = $this->arguments['jsdebug'];
        }
        $this->arguments['data'] = $data;
        foreach ($data as $dataAttributeKey => $dataAttributeValue) {
            $this->tag->addAttribute('data-' . $dataAttributeKey, $dataAttributeValue);
        }
    }

    /**
     * addAdditionalAttributes
     *
     * merge our own additionalAttributes with the ones coming from the viewhelper arguments (if any). The latter takes
     * precedence, i.e.: It is possible to overwrite any default param from the viewHelper if necessary.
     *
     * @return void
     */
    public function addAdditionalAttributes()
    {
        $additionalAttributes = null;

        $extraAdditionalAttributes = [
            'sizes' => '100vw',
        ];

        if ($this->isLazyLoading()) {
            $extraAdditionalAttributes['srcset'] = $this->getPlaceholder($this->arguments['cropVariant']);
        } else {
            $extraAdditionalAttributes['srcset'] = $this->getSrcSetString();
        }

        if ($this->hasArgument('additionalAttributes') && is_array($this->arguments['additionalAttributes'])) {
            $additionalAttributes = array_merge($extraAdditionalAttributes, $this->arguments['additionalAttributes']);
        } else {
            $additionalAttributes = $extraAdditionalAttributes;
        }

        // argument sizes always overwrites $additionalAttributes['sizes']
        if ($this->hasArgument('sizes')) {
            $additionalAttributes['sizes'] = $this->arguments['sizes'];
        }

        $this->tag->addAttributes($additionalAttributes);
        $this->arguments['additionalAttributes'] = $additionalAttributes;
    }

    /** getSrcSetString
     * @return string
     */
    public function getSrcSetString()
    {
        return $this->imageUtility->getSrcSetString($this->cropVariants[$this->arguments['cropVariant']]['candidates']);
    }
}
