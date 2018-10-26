<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Functional\Traits;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 */
trait CreateFileReference
{

    /**
     * Create a file reference and return it
     *
     * @param FileInterface|string $file
     * @param string $tablenames
     * @param string $fieldname
     * @param int $uid_foreign
     * @param int $pid
     * @param $properties $properties to set on the file reference
     *
     * @return array
     */
    public function createAndReturnFileReference(
        $file,
        $tablenames = 'tt_content',
        $fieldname = 'assets',
        $uid_foreign = 1,
        $pid = 0,
        $properties = []
    ) {
        if (!$file instanceof FileInterface) {
            $file = $this->storage->getFile($file);
        }

        // Assemble DataHandler data
        $newId = 'NEW1234';
        $data = [];
        $baseData = [
            'uid_local' => $file->getUid(),
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
            return false;
        }
        $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        /** @var array $files */
        return $fileRepository->findByRelation($tablenames, $fieldname, $uid_foreign);
    }
}
