<?php
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AbstractViewHelper
 * @package C1\AdaptiveImages\Tests\Unit\ViewHelpers
 */
abstract class AbstractViewHelperTest extends UnitTestCase
{
    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
    }


//    protected function mockImageUtility()
//    {
//        $test = $this;
//
//        $imageUtilityMock = $this->getMockBuilder(ImageUtility::class)
//            ->setMethods(['getDebugAnnotation'])
//            ->getMock();
//
//        $imageUtilityMock
//            ->method('getDebugAnnotation')
//            ->will($this->returnCallback(function ($width, $height, $ratio = null) {
//                return $width . 'x'. $height .'(' . $ratio .  ')';
//            }));
//
////
////
////        $imageUtilityMock
////            ->method('getImageUri')
////            ->will($this->returnCallback(function ($file, $absolute) {
////                print_r($file->getProperties());
////                return (($absolute) ? 'http://domain.tld' : '') . '/image@' . $file->getProperty('width') . '.jpg';
////            }));
//
//        return $imageUtilityMock;
//    }

    protected function mockImageService()
    {
        $test = $this;

        $imageServiceMock = $this->getMockBuilder(ImageService::class)
            ->setMethods(['applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->will($this->returnCallback(function ($file, $instructions) use ($test) {
                // no upscaling of images
                $instructions['width'] = min(intval($file->getProperty('width')), intval($instructions['width']));
                //print_r($instructions);
                return $test->mockFileObject($instructions);
            }));

        $imageServiceMock
            ->method('getImageUri')
            ->will($this->returnCallback(function ($file, $absolute) {
                //print_r($file->getProperties());
                return (($absolute) ? 'http://domain.tld' : '') . '/image@' . $file->getProperty('width') . '.jpg';
            }));

        return $imageServiceMock;
    }

    protected function mockFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty', 'getProperties'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                return $properties[$property];
            }));

        $fileMock
            ->method('getProperties')
            ->will($this->returnCallback(function () use ($properties) {
                return $properties;
            }));

        return $fileMock;
    }

    protected function mockObjectManager()
    {
        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $managerMock
            ->method('get')
            ->will($this->returnCallback(function ($className) {
                $arguments = func_get_args();
                array_shift($arguments);
                return new $className(...$arguments);
            }));

        return $managerMock;
    }

}