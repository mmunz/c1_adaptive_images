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
abstract class AbstractViewHelperTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

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
            ->setMethods()
            ->getMock();

        return $imageUtilityMock;
    }

    protected function mockImageService()
    {
        $test = $this;

        $imageServiceMock = $this->getMockBuilder(ImageService::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->will($this->returnCallback(function ($file, $instructions) use ($test) {
                // no upscaling of images
                $newProperties = $file->getProperties();
                $newProperties['width'] = min(intval($file->getProperty('width')), intval($instructions['width']));
                return $test->mockProcessedFileObject($newProperties);
            }));

        $imageServiceMock
            ->method('getImageUri')
            ->will($this->returnCallback(function ($file, $absolute) {
                return (($absolute) ? 'http://domain.tld' : '') . '/image@' . $file->getProperty('width') . '.jpg';
            }));

        return $imageServiceMock;
    }

    protected function mockFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty', 'getProperties', 'getContents', 'hasProperty'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
                return false;
            }));

        $fileMock
            ->method('getProperties')
            ->will($this->returnCallback(function () use ($properties) {
                return $properties;
            }));

        $fileMock
            ->method('hasProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return true;
                }
                return false;
            }));

        $fileMock
            ->method('getContents')
            ->will($this->returnCallback(function () {
                return 'the images content';
            }));

        return $fileMock;
    }

    protected function mockProcessedFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(ProcessedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty', 'getProperties', 'getContents', 'hasProperty'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
                return false;
            }));

        $fileMock
            ->method('getProperties')
            ->will($this->returnCallback(function () use ($properties) {
                return $properties;
            }));

        $fileMock
            ->method('hasProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return true;
                }
                return false;
            }));

        $fileMock
            ->method('getContents')
            ->will($this->returnCallback(function () {
                return 'the images content';
            }));

        return $fileMock;
    }
}
