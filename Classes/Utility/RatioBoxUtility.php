<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Utility;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class RatioBoxUtility
 */
class RatioBoxUtility
{
    private PageRenderer $pageRenderer;

    private CropVariantUtility $cropVariantUtility;

    private array $ratioBoxClassNames = [];

    private string $ratioBoxBase = '';

    private TagBuilder $tagBuilder;

    public function __construct(PageRenderer $pageRenderer, CropVariantUtility $cropVariantUtility)
    {
        $this->pageRenderer = $pageRenderer;
        $this->cropVariantUtility = $cropVariantUtility;
        $this->tagBuilder = new TagBuilder();
    }

    public function setRatioBoxBase(string $ratioBoxBase = 'ratio-box'): void
    {
        $this->ratioBoxBase = $ratioBoxBase;
    }

    /**
     * Removes unwanted characters from css ClassNames
     */
    public function sanitizeCssClassName(string $class): string
    {
        $class = \strtolower($class);
        // remove all characters not allowed in HTML class names
        $regex = '/[^\\x{002D}\\x{0030}-\\x{0039}\\x{0041}-\\x{005A}\\x{005F}\\x{0061}-\\x{007A}\\x{00A1}-\\x{FFFF}]/u';
        $class = \preg_replace($regex, '', $class);
        $class = \preg_replace("/[\s_]/", '-', $class ?? '');
        return $class ?? '';
    }

    /**
     *
     * Returns a class name for the ratio box (for intrinsic ratio css)
     *
     * Because ratio can be a float and dots are not allowed inside css class names dots in $ratio are replaced with
     * 'dot'. After that the resulting string is also filtered to make sure it does only contain valid chars to use in
     * css class names.
     *
     * @param int|float $ratio
     * @param string|null $mq
     * @return string
     */
    public function getRatioClassForCropVariant($ratio, $mq = null)
    {
        $ratioBoxBase = $this->ratioBoxBase;

        if ($mq) {
            $ratioBoxClass = sprintf(
                '%s--%s-%s',
                $ratioBoxBase,
                $this->sanitizeCssClassName($mq),
                \preg_replace('/\./i', 'dot', (string)$ratio)
            );
        } else {
            $ratioBoxClass = sprintf(
                '%s--%s',
                $ratioBoxBase,
                \preg_replace('/\./i', 'dot', (string)$ratio)
            );
        }

        return $this->sanitizeCssClassName($ratioBoxClass);
    }

    /**
     *
     * Get the default style for the ratio box
     * @param int|float $ratio
     * @param string $mq
     * @return string
     */
    public function getRatioBoxStyle($ratio, $mq = null)
    {
        if ($mq) {
            return sprintf(
                '@media %s{.%s.%s{padding-bottom:%s%%}}',
                $mq,
                $this->ratioBoxBase,
                $this->getRatioClassForCropVariant($ratio, $mq),
                $ratio
            );
        } else {
            return sprintf(
                '.%s{padding-bottom:%s%%}',
                $this->getRatioClassForCropVariant($ratio, null),
                $ratio
            );
        }
    }

    /**
     *
     * Add inline css to the header for generated ratio-box class names
     *
     * @param string $class
     * @param string $css
     * @param bool $compress
     * @return void
     */
    public function addStyleToHeader($class, $css, $compress = true)
    {
        $this->pageRenderer->addCssInlineBlock($class, $css, $compress);
    }

    /**
     * return ratio box classNames
     *
     * @param array $cropVariants
     *
     * @return array
     */
    public function getRatioBoxClassNames(array $cropVariants)
    {
        $this->ratioBoxClassNames = [];
        $this->ratioBoxClassNames[] = $this->ratioBoxBase;
        foreach (array_reverse($cropVariants) as $cropVariantKey => $cropVariantConfig) {
            $mq = $cropVariantConfig['media'] ?? null;
            $className = $this->getRatioClassForCropVariant($cropVariantConfig['ratio'], $mq);
            $this->ratioBoxClassNames[] = $className;
            $css = $this->getRatioBoxStyle($cropVariantConfig['ratio'], $mq);
            $this->addStyleToHeader($className, $css, true);
        }

        return $this->ratioBoxClassNames;
    }

    /**
     * Build the ratio box tag
     * @param string $content
     * @param array $classNames
     * @return string
     */
    public function buildRatioBoxTag(string $content, array $classNames)
    {
        $this->tagBuilder->reset();
        $this->tagBuilder->setTagName('div');
        $this->tagBuilder->setContent($content);
        $this->tagBuilder->addAttribute('class', implode(' ', $classNames));
        return $this->tagBuilder->render();
    }

    /**
     * Wrap $content inside a ratio box
     * @param string $content
     * @param FileInterface $file
     * @param array $mediaQueries
     * @return string
     */
    public function wrapInRatioBox(string $content, FileInterface $file, array $mediaQueries)
    {
        $this->cropVariantUtility->setCropVariantCollection($file);

        $cropVariants = $this->cropVariantUtility->getCropVariants($mediaQueries);

        //DebuggerUtility::var_dump($cropVariants);
        $this->setRatioBoxBase('rb');
        $classNames = $this->getRatioBoxClassNames($cropVariants);
        return $this->buildRatioBoxTag($content, $classNames);
    }
}
