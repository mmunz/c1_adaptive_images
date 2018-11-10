<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * = Examples =
 * In the example below the {file} object has two cropVariants:
 * default: 16:9
 * mobile: 4:3
 * <code title="Default">
 *  <ai:ratioBox file="{file}" mediaQueries="{mobile: '(max-width:767px)', default: ''}">
 *    <f:image image="{file} alt="foo" />
 *  </ai:ratioBox>
 * </code>
 * <output>
 * <div class="rb rb--62dot5 rb--max-width767px-75">
 *  <img src="..." alt="foo">
 * </div>
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
     * @var \C1\AdaptiveImages\Utility\RatioBoxUtility
     * @inject
     */
    protected $ratioBoxUtility;

    /** @var \C1\AdaptiveImages\Utility\CropVariantUtility
     *  @inject
     */
    protected $cropVariantUtility;

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
     * Returns the cropVariants array
     *
     * @param string $content
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string
     */
    public function render()
    {
        /** @var FileInterface $file */
        $file = $this->arguments['file'];

        if (is_null($file)) {
            throw new Exception('You must specify a File object.', 1522176433);
        }

        $mediaQueries = $this->arguments['mediaQueries'];
        if (array_key_exists('default', $mediaQueries) === false) {
            $mediaQueries['default'] = '';
        }

        $this->cropVariantUtility->setCropVariantCollection($file);
        $cropVariants = $this->cropVariantUtility->getCropVariants($mediaQueries);

        $this->ratioBoxUtility->setRatioBoxBase('rb');
        $classNames = $this->ratioBoxUtility->getRatioBoxClassNames($cropVariants);

        $this->tag->setTagName('div');
        $this->tag->setContent($this->renderChildren());
        $this->tag->addAttribute('class', implode(' ', $classNames));

        return $this->tag->render();
    }
}
