<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Class AbstractViewHelper
 */
abstract class AbstractViewHelperTestCase extends UnitTestCase
{
    /**
     * @var ViewHelperInterface
     */
    protected $viewHelper;

    /**
     * set up
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function mockImageUtility()
    {
        $imageUtilityMock = $this->getMockBuilder(ImageUtility::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $imageUtilityMock;
    }

    protected function mockImageService()
    {
        $test = $this;

        $imageServiceMock = $this->getMockBuilder(ImageService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->willReturnCallback(function ($file, $instructions) use ($test) {
                // no upscaling of images
                $newProperties = $file->getProperties();
                $newProperties['width'] = min(intval($file->getProperty('width')), intval($instructions['width']));
                return $test->mockProcessedFileObject($newProperties);
            });

        $imageServiceMock
            ->method('getImageUri')
            ->willReturnCallback(fn ($file, $absolute) => (($absolute) ? 'http://domain.tld' : '') . '/image@' . $file->getProperty('width') . '.jpg');

        return $imageServiceMock;
    }

    protected function mockFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProperty', 'getProperties', 'getContents', 'hasProperty'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
                return false;
            });

        $fileMock
            ->method('getProperties')
            ->willReturnCallback(fn () => $properties);

        $fileMock
            ->method('hasProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return true;
                }
                return false;
            });

        $fileMock
            ->method('getContents')
            ->willReturnCallback(fn () => 'the images content');

        return $fileMock;
    }

    protected function mockProcessedFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(ProcessedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProperty', 'getProperties', 'getContents', 'hasProperty'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
                return false;
            });

        $fileMock
            ->method('getProperties')
            ->willReturnCallback(fn () => $properties);

        $fileMock
            ->method('hasProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return true;
                }
                return false;
            });

        $fileMock
            ->method('getContents')
            ->willReturnCallback(fn () => 'the images content');

        return $fileMock;
    }
}
