<?php
declare(strict_types=1);

namespace T23\SwaCity\Tests\Unit\Controller;

/**
 * Class ActivationControllerTest
 * @package T23\SwaCity\Tests\Unit\Controller
 *
 */
class RatioBoxTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{


    /** @var array */
    protected $testExtensionsToLoad = ['../../../c1_adaptive_images'];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['core', 'extensionmanager', 'frontend', 'fluid', 'fluid_styled_content'];


    /** @var int $pageId to use in these tests */
    protected $pageId = 1;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        parent::setUp();

        $this->importDataSet('EXT:c1_adaptive_images/Tests/Functional/Fixtures/Database/pages.xml');
        $this->importDataSet('EXT:c1_adaptive_images/Tests/Functional/Fixtures/Database/tt_content.xml');

        /* activation controller tested on PID 1 */
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.txt',
                'EXT:c1_adaptive_images/Tests/Functional/Fixtures/TypoScript/common.t3s'
            ]
        );
    }

    /**
     * @test
     *
     * Request with empty token returns redirect to the error action
     */
    public function helloWorld()
    {
        $response = $this->getFrontendResponse(1);
        $this::assertContains(
            'works',
            $response->getContent()
        );
    }

}