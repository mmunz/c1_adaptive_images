<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use C1\AdaptiveImages\Utility\RatioBoxUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
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
    /** @var array $cropVariants */
    protected $cropVariants;

    public function __construct(
        ImageUtility $imageUtility,
        RatioBoxUtility $ratioBoxUtility,
        ImagePlaceholderUtility $imagePlaceholderUtility,
        ImageService $imageService
    ) {
        parent::__construct($imageUtility, $ratioBoxUtility, $imagePlaceholderUtility, $imageService);
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
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
    public function initialize(): void
    {
        parent::initialize();
        $this->imageUtility->setOriginalFile($this->arguments['image']);
        $cropVariantForImg = [
            $this->arguments['cropVariant'] => [
                'srcsetWidths' => $this->arguments['srcsetWidths'],
                'aspectRatio' => $this->arguments['aspectRatio'] ?? null,
            ]
        ];
        $cropVariantsMerged = array_merge($this->arguments['sources'], $cropVariantForImg);
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

        if ($this->arguments['ratiobox'] === true) {
            $mq = [];
            $aspectRatios = [];

            if ($this->hasArgument('aspectRatio') && $this->arguments['aspectRatio'] > 0) {
                $aspectRatios[$this->arguments['cropVariant']] = $this->arguments['aspectRatio'];
            }

            foreach ($this->cropVariants as $key => $config) {
                $mq[$key] = $config['media'] ?? null;
                if (isset ($config['aspectRatio']) && $config['aspectRatio'] > 0) {
                    $aspectRatios[$key] = $config['aspectRatio'];
                }
            }

            return $this->ratioBoxUtility->wrapInRatioBox(
                $picture,
                $this->arguments['image'],
                $mq,
                $aspectRatios,
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
}
