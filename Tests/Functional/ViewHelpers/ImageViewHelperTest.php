<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Functional\ViewHelpers;

/**
 * Class ImageViewHelperTest
 *
 */
class ImageViewHelperTest extends AbstractFunctionalTest
{
    protected $patterns = [
        'base64' => '[a-zA-Z0-9/+]*={0,2}'
    ];

    /**
     * getImgTags
     * returns image Tags from a html string
     * @param string $html
     * @return \DOMNodeList
     */
    protected function getImgTags($html)
    {
        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $images = $dom->getElementsByTagName('img');
        return $images;
    }

    /**
     * @param $html
     * @return \DOMElement
     */
    protected function getFirstImgTag($html)
    {
        $imgTags = $this->getImgTags($html);
        $imgTag = $imgTags[0];
        return $imgTag;
    }

    /**
     * getFirstImgTagAttributes
     * @param $html
     * @return array
     */
    protected function getFirstImgTagAttributes($html)
    {
        $tag = $this->getFirstImgTag($html);
        $tagAttributes = [];
        if ($tag->hasAttributes()) {
            foreach ($tag->attributes as $attr) {
                $tagAttributes[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $tagAttributes;
    }

    /**
     * @test
     *
     * test if the viewhelper can render the img tag
     */
    public function canRenderImage()
    {
        $expectedAttributes = [
            'sizes' => '100vw',
            'srcset' => 'data:image/jpeg;base64,' . $this->patterns['base64'] . ' [0-9]?w',
            'data-sizes' => 'auto',
            'data-srcset' => 'fileadmin/_processed_/.*.jpg 200w,fileadmin/_processed_/.*.jpg 400w',
            'src' => 'fileadmin/_processed_/.*.jpg',
            'width' => 600,
            'height' => 375,
            'alt' => '',
        ];

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}',
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=ImageViewHelper&placeholderWidth=4&debug=1&lazy=1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $tagAttributes = $this->getFirstImgTagAttributes($response->getContent());
        foreach ($expectedAttributes as $key => $value) {
            $this->assertRegExp('@^' . $value . '$@', $tagAttributes[$key]);
        }
    }

    /**
     * @test
     *
     * test if the srcset attribute is properly filled by a placeholder
     */
    public function placeHolderAsImg()
    {
        $expectedAttributes = [
            'srcset' => 'fileadmin/_processed_/.*.jpg 4w',
        ];

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}',
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=ImageViewHelper&placeholderWidth=4&placeholderInline=0&lazy=1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $tagAttributes = $this->getFirstImgTagAttributes($response->getContent());
        foreach ($expectedAttributes as $key => $value) {
            $this->assertRegExp('@^' . $value . '$@', $tagAttributes[$key]);
        }
    }

    /**
     * @test
     *
     * test if the attributes are properly filled when lazy loading is disabled
     */
    public function lazyLoadingDisabled()
    {
        $expectedAttributes = [
            'srcset' => 'fileadmin/_processed_/.*.jpg 200w,fileadmin/_processed_/.*.jpg 400w',
        ];

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}',
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=ImageViewHelper&placeholderWidth=4&lazy=0';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $tagAttributes = $this->getFirstImgTagAttributes($response->getContent());
        foreach ($expectedAttributes as $key => $value) {
            $this->assertRegExp('@^' . $value . '$@', $tagAttributes[$key]);
        }
    }
}
