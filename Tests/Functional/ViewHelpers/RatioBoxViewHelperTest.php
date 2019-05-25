<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Functional\ViewHelpers;

/**
 * Class ActivationControllerTest
 *
 */
class RatioBoxViewHelperTest extends AbstractFunctionalTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * inline css style tag is added to the HEAD of the page with correct rules
     */
    public function cssIsAddedToHeadForDefaultCropVariant()
    {
        $properties = [];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);
        // @ToDo - can only pass strings for now, find out how to pass arrays
        // $arguments = '&mq-mobile=(max-width:680px)';
        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = "<style type=\"text/css\">\n/*<![CDATA[*/\n<!-- \n/*rb--62dot5*/\n.rb--62dot5{padding-bottom:62.5%}\n/*rb--max-width767px-62dot5*/\n@media (max-width:767px){.rb.rb--max-width767px-62dot5{padding-bottom:62.5%}}\n-->\n/*]]>*/\n</style>";
        $this::assertContains($expected, $response->getContent());
    }

    /**
     * @test
     *
     * test that a ratio box wrapper element (per default a DIV) with correct values exists
     */
    public function ratioBoxWrapperForDefaultCropVariantExists()
    {
        $properties = [];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = '<div class="rb rb--62dot5 rb--max-width767px-62dot5">';
        $this::assertContains($expected, $response->getContent());
    }

    /**
     * @test
     *
     * inline css style tag is added to the HEAD of the page with correct rules
     */
    public function cssIsAddedToHeadForMultipleCropVariants()
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"height":0.8992,"width":1,"x":0,"y":0.0096},"selectedRatio":"16:9","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}},"mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}'
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);
        // @ToDo - can only pass strings for now, find out how to pass arrays
        // $arguments = '&mq-mobile=(max-width:680px)';
        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = "<style type=\"text/css\">\n/*<![CDATA[*/\n<!-- \n/*rb--56dot2*/\n.rb--56dot2{padding-bottom:56.2%}\n/*rb--max-width767px-74dot86*/\n@media (max-width:767px){.rb.rb--max-width767px-74dot86{padding-bottom:74.86%}}\n-->\n/*]]>*/\n</style>\n";
        $this::assertContains($expected, $response->getContent());
    }

    /**
     * @test
     *
     * test that a ratio box wrapper element (per default a DIV) with correct values exists like before, but with
     * multiple cropVariants
     */
    public function ratioBoxWrapperWithMultipleCropVariantsExists()
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"height":0.8992,"width":1,"x":0,"y":0.0096},"selectedRatio":"16:9","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}},"mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}'
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = '<div class="rb rb--56dot2 rb--max-width767px-74dot86">';
        $this::assertContains($expected, $response->getContent());
    }
}
