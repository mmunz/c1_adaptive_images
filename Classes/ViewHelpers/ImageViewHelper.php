<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use C1\AdaptiveImages\Utility\RatioBoxUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

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
class ImageViewHelper extends AbstractImageBasedViewHelper
{

    public function __construct(ImageUtility $imageUtility, RatioBoxUtility $ratioBoxUtility, ImagePlaceholderUtility $imagePlaceholderUtility, ImageService $imageService)
    {
        $this->imageUtility = $imageUtility;
        $this->ratioBoxUtility = $ratioBoxUtility;
        $this->imagePlaceholderUtility = $imagePlaceholderUtility;
        $this->imageService = $imageService;
        parent::__construct($imageUtility, $ratioBoxUtility, $imagePlaceholderUtility, $imageService);
    }

    /**
     * Sets and initializes $this->imageUtility
     * After that manipulate the data and additionalAttributes arguments.
     *
     * Manipulate the data and additionalAttributes arguments just before the render method
     *
     * @api
     */
    public function initialize()
    {
        parent::initialize();

        $this->imageUtility->setOriginalFile($this->arguments['image']);
        // @extensionScannerIgnoreLine
        $this->imageUtility->init(
            [
                'debug' => $this->arguments['debug'],
                'cropVariants' => [
                    $this->arguments['cropVariant'] => [
                        'srcsetWidths' => $this->arguments['srcsetWidths']
                    ]
                ]
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
        $image = parent::render();

        if ($this->arguments['ratiobox'] === true) {
            $mediaQueries = [
                $this->arguments['cropVariant'] => ''
            ];
            return $this->ratioBoxUtility->wrapInRatioBox($image, $this->arguments['image'], $mediaQueries);
        } else {
            return $image;
        }
    }
}
