<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

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
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $ratioBoxUtility;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('file', FileInterface::class, 'a file or file reference', true);
        $this->registerArgument(
            'mediaQueries',
            'array',
            'media queries to use for the different cropVariants',
            false,
            [['default' => '']]
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
        return $this->ratioBoxUtility->wrapInRatioBox(
            $this->renderChildren(),
            $this->arguments['file'],
            $this->arguments['mediaQueries']
        );
    }
}
