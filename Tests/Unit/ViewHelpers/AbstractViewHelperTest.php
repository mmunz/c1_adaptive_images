<?php
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\SvgUtility;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * Class AbstractViewHelper
 */
abstract class AbstractViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
    }

    protected function mockImageUtility()
    {
        $imageUtilityMock = $this->getMockBuilder(ImageUtility::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOriginalFile', 'getCropAreaForVariant'])
            ->getMock();

        $imageUtilityMock
            ->method('setOriginalFile')
            ->will($this->returnCallback(function ($file) {
                return true;
            }));

        $imageUtilityMock
            ->method('getCropAreaForVariant')
            ->will($this->returnCallback(function ($cropVariant) {
                return '';
            }));

        return $imageUtilityMock;
    }

    protected function mockSvgUtility()
    {
        $svgUtilityMock = $this->getMockBuilder(SvgUtility::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSvgPlaceholder'])
            ->getMock();

        $svgUtilityMock
            ->method('getSvgPlaceholder')
            ->will($this->returnCallback(function ($width, $height, $content) {
                if ($content) {
                    return 'data:image/svg+xml;base64,ABCDEFG...with_content...';
                }
                return 'data:image/svg+xml;base64,ABCDEFG...';
            }));

        return $svgUtilityMock;
    }

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
            ->setMethods(['getProperty', 'getProperties', 'getContents'])
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

        $fileMock
            ->method('getContents')
            ->will($this->returnCallback(function () {
                return 'the images content';
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

    /**
     * Helper function to merge arguments with default arguments according to their registration
     * This usually happens in ViewHelperInvoker before the view helper methods are called
     *
     * This is an extended variant of the method in ViewHelperBaseTestcase which also validates the the viewHelper
     * arguments.
     *
     * @param ViewHelperInterface $viewHelper
     * @param array $arguments
     */
    protected function setArgumentsUnderTest($viewHelper, array $arguments = [])
    {
        $expectedViewHelperArguments = $viewHelper->prepareArguments();
        // Rendering process
        $evaluatedArguments = [];
        $undeclaredArguments = [];

        try {
            foreach ($expectedViewHelperArguments as $argumentName => $argumentDefinition) {
                if (isset($arguments[$argumentName])) {
                    /** @var NodeInterface|mixed $argumentValue */
                    $argumentValue = $arguments[$argumentName];
                    $argumentValue instanceof NodeInterface ? $argumentValue->ab($this->renderingContext) : $argumentValue;
                    $evaluatedArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($this->renderingContext) : $argumentValue;
                } else {
                    $actualArgumentNames = array_keys($arguments);
                    foreach ($expectedViewHelperArguments as $expectedArgument) {
                        if ($expectedArgument->isRequired() && !in_array($expectedArgument->getName(), $actualArgumentNames)) {
                            throw new Exception('Required argument "' . $expectedArgument->getName() . '" was not supplied.', 1237823699);
                        }
                    }
                    $evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
                }
            }
            foreach ($arguments as $argumentName => $argumentValue) {
                if (!array_key_exists($argumentName, $evaluatedArguments)) {
                    $undeclaredArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($this->renderingContext) : $argumentValue;
                }
            }

            $viewHelper->setRenderingContext($this->renderingContext);
            $viewHelper->setArguments($evaluatedArguments);
            $viewHelper->handleAdditionalArguments($undeclaredArguments);
            return $viewHelper->initializeArgumentsAndRender();
        } catch (Exception $error) {
            return $this->renderingContext->getErrorHandler()->handleViewHelperError($error);
        }
        $viewHelper->setArguments($arguments);
    }
}
