<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\ViewHelpers;

use C1\AdaptiveImages\Tests\Functional\Traits\CreateFileReference;
use C1\AdaptiveImages\Tests\Functional\Traits\GetFrontendResponse;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ActivationControllerTest
 *
 */
abstract class AbstractFunctionalTest extends FunctionalTestCase
{
    use GetFrontendResponse;
    use CreateFileReference;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/c1_adaptive_images',
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'fluid_styled_content',
    ];

    /**
     * @var array
     */
    protected $pathsToLinkInTestInstance = [
        '../../../../../../Tests/Functional/Fixtures/fileadmin/user_upload' => 'fileadmin/user_upload',
    ];

    /**
     * @var int $pageId to use in these tests
     */
    protected $pageId = 1;

    /**
     * @var ResourceStorage
     */
    protected $storage;

    /**
     *
     */
    protected $typoScriptIncludes = [
        'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
        'EXT:c1_adaptive_images/Configuration/TypoScript/setup.txt',
        __DIR__ . '/../Fixtures/TypoScript/common.t3s',
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/Database/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/Database/tt_content.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/Database/sys_file_storage.xml');

        /* activation controller tested on PID 1 */
        $this->setUpFrontendRootPage(
            1,
            $this->typoScriptIncludes
        );

        /** @var $backendUser \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
        $backendUser = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
        $backendUser->user['admin'] = 1;
        $backendUser->user['uid'] = 1;
        $backendUser->workspace = 0;
        $GLOBALS['BE_USER'] = $backendUser;

        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $this->storage = $resourceFactory->getStorageObject(1);
        $this->storage->setEvaluatePermissions(false);

        $indexer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\Indexer::class, $this->storage);
        $indexer->processChangesInStorages();
    }
}
