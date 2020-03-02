<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\ViewHelpers;

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

    /** getSrcSetString
     * @return string
     */
    public function getSrcSetString()
    {
        $cropVariants = $this->imageUtility->getCropVariants();
        return $this->imageUtility->getSrcSetString($cropVariants[$this->arguments['cropVariant']]['candidates']);
    }
}
