<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\ViewHelpers;

/**
 * Class ActivationControllerTest
 *
 */
class RatioBoxTest extends AbstractFunctionalTest
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
    public function cssIsAddedToHead()
    {
        $properties = [];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);
        // @ToDo - can only pass strings for now, find out how to pass arrays
        // $arguments = '&mq-mobile=(max-width:680px)';
        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = "<style type=\"text/css\">\n/*<![CDATA[*/\n<!-- \n/*rb--62dot5*/\n.rb--62dot5{padding-bottom:62.5%}\n-->\n/*]]>*/\n</style>\n";
        $this::assertContains($expected, $response->getContent());
    }

    /**
     * @test
     *
     * test that a ratio box wrapper element (per default a DIV) with correct values exists
     */
    public function ratioBoxWrapperExists()
    {
        $properties = [];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);

        $arguments = '&mode=RatioBox1';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = '<div class="rb rb--62dot5 rb--62dot5"><img';
        $this::assertContains($expected, $response->getContent());
    }
}
