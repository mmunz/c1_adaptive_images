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
     * Custom alt text is shown
     */
    public function customAltTextIsShown()
    {
        $properties = [
            'alternative' => 'Using alt text is important for accessibility!'
        ];

        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);
        $arguments = '&mode=testmode';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $this::assertContains(
            'Using alt text is important for accessibility!',
            $response->getContent()
        );
    }
}
