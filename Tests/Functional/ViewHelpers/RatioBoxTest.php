<?php
declare(strict_types=1);

namespace T23\SwaCity\Tests\Unit\Controller;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ActivationControllerTest
 *
 */
class RatioBoxTest extends FunctionalTestCase
{

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
            [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:c1_adaptive_images/Configuration/TypoScript/setup.txt',
                __DIR__ . '/../Fixtures/TypoScript/common.t3s',
            ]
        );

        /** @var $backendUser \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
        $backendUser = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
        $backendUser->user['admin'] = 1;
        $backendUser->user['uid'] = 1;
        $backendUser->workspace = 0;
        $GLOBALS['BE_USER'] = $backendUser;

        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $this->storage = $resourceFactory->getStorageObject(1);

        $indexer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\Indexer::class, $this->storage);
        $indexer->processChangesInStorages();
    }

    /**
     * @test
     *
     * Test if the output contains at least one processed file
     */
    public function processedFileinOutput()
    {
        /** @var FileInterface $fileObject */
        $fileObject = $this->storage->getFile('user_upload/nightlife-4.jpg');

        $this->createAndReturnFileReference($fileObject);

        $this->storage->setEvaluatePermissions(false);
        $response = $this->getFrontendResponse(1);
        $this::assertContains(
            'fileadmin/_processed_/',
            $response->getContent()
        );
    }

    /**
     * @test
     *
     * Custom alt text is shown
     */
    public function customAltTextIsShown()
    {
        $this->storage->setEvaluatePermissions(false);
        $properties = [
            'alternative' => 'Using alt text is important for accessibility!'
        ];

        /** @var FileInterface $fileObject */
        $fileObject = $this->storage->getFile('user_upload/nightlife-4.jpg');

        $this->createAndReturnFileReference($fileObject, 'tt_content', 'assets', 1, 0, $properties);

        $response = $this->getFrontendResponse(1);
        print_r($response->getStatus());

        $this::assertContains(
            'Using alt text is important for accessibility!',
            $response->getContent()
        );
    }

    /**
     * Create a file reference and return it
     *
     * @param FileInterface $fileObject
     * @param string $tablenames
     * @param string $fieldname
     * @param int $uid_foreign
     * @param int $pid
     * @param $properties $properties to set on the file reference
     *
     * @return array
     */
    public function createAndReturnFileReference(
        $fileObject,
        $tablenames = 'tt_content',
        $fieldname = 'assets',
        $uid_foreign = 1,
        $pid = 0,
        $properties = []
    ) {
        // Assemble DataHandler data
        $newId = 'NEW1234';
        $data = [];
        $baseData = [
            'uid_local' => $fileObject->getUid(),
            'tablenames' => $tablenames,
            'uid_foreign' => $uid_foreign,
            'fieldname' => $fieldname,
            'pid' => $pid,
        ];
        $data['sys_file_reference'][$newId] = array_merge($baseData, $properties);
        $data['tt_content'][1][$fieldname] = $newId;

        // Get an instance of the DataHandler and process the data
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (count($dataHandler->errorLog) !== 0) {
            print_r($dataHandler->printLogErrorMessages());
            return false;
        }
        $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        /** @var array $fileObjects */
        return $fileRepository->findByRelation($tablenames, $fieldname, $uid_foreign);
    }
}
