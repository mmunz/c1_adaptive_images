<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\TagUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

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
class PictureViewHelper extends AbstractImageBasedViewHelper
{
    /**
     * @var \C1\AdaptiveImages\Utility\TagUtility
     */
    protected $tagUtility;

    /**
     * @param TagUtility $tagUtility
     */
    public function injectTagUtility(TagUtility $tagUtility)
    {
        $this->tagUtility = $tagUtility;
    }

    /** @var array $cropVariants */
    protected $cropVariants;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'sources',
            'array',
            'media queries to use for the different cropVariants',
            false,
            [['default' => '']]
        );
    }

    /**
     * Sets and initializes $this->imageUtility
     * Sets $this->cropVariants
     * Manipulate the data and additionalAttributes arguments just before the render method
     *
     * @api
     */
    public function initialize()
    {
        parent::initialize();
        $this->imageUtility->setOriginalFile($this->arguments['image']);
        $cropVariantForImg = [
            $this->arguments['cropVariant'] => [
                'srcsetWidths' => $this->arguments['srcsetWidths']
            ]
        ];
        $cropVariantsMerged = array_merge_recursive($this->arguments['sources'], $cropVariantForImg);
        // @extensionScannerIgnoreLine
        $this->imageUtility->init(
            [
                'debug' => $this->arguments['debug'],
                'cropVariants' => $cropVariantsMerged
            ]
        );
        $this->cropVariants = $this->imageUtility->getCropVariants();

        $this->addAdditionalAttributes();
        $this->addDataAttributes();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render()
    {
        $imageTag = parent::render();
        $sources = $this->cropVariants;
        unset($sources[$this->arguments['cropVariant']]);
        $picture = $this->buildPictureTag($imageTag, $sources);

        $mq = [];
        foreach ($this->cropVariants as $key => $config) {
            $mq[$key] = $config['media'];
        }

        if ($this->arguments['ratiobox'] === true) {
            return $this->ratioBoxUtility->wrapInRatioBox(
                $picture,
                $this->arguments['image'],
                $mq
            );
        } else {
            return $picture;
        }
    }

    /**
     * Build a source tag with media and srcset attributes
     * @param string $media
     * @param string $srcset
     * @param string $cropVariant
     * @return string
     */
    public function buildSourceTag(string $media, string $srcset, string $cropVariant)
    {
        $tagBuilder = new TagBuilder('source');
        if ($media) {
            $tagBuilder->addAttribute('media', $media);
        }
        if ($srcset) {
            if ($this->arguments['lazy']) {
                $tagBuilder->addAttribute('data-srcset', $srcset);
                $tagBuilder->addAttribute('data-sizes', 'auto');
                $tagBuilder->addAttribute('srcset', $this->getPlaceholder($cropVariant));
            } else {
                $tagBuilder->addAttribute('srcset', $srcset);
            }
            $tagBuilder->addAttribute('sizes', $this->arguments['additionalAttributes']['sizes']);
        }
        return $tagBuilder->render();
    }

    /**
     * Build the picture tag
     * @param string $imgTag
     * @param array $sources
     * @param bool $lazy
     * @return string
     */
    public function buildPictureTag(string $imgTag, array $sources)
    {
        $content = '';
        foreach ($sources as $key => $config) {
            $content .= $this->buildSourceTag($config['media'], $config['srcset'], $key);
        }
        $content .= $imgTag;
        $tagBuilder = new TagBuilder('picture');
        $tagBuilder->setContent($content);
        return $tagBuilder->render();
    }

    /** getSrcSetString
     * @return string
     */
    public function getSrcSetString()
    {
        return $this->imageUtility->getSrcSetString($this->cropVariants[$this->arguments['cropVariant']]['candidates']);
    }
}
