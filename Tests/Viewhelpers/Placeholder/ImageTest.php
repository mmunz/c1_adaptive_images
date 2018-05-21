<?php

namespace C1\ImageRenderer\Tests\Unit\Viewhelpers\Placeholder;

use C1\ImageRenderer\Utility\ImageUtility;
use C1\ImageRenderer\ViewHelpers\Placeholder\GetViewHelper;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageTest extends ViewHelperBaseTestcase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccessibleMockObjectInterface|GetViewHelper
     */
    protected $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(GetViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /** @test */
    public function renderReturnsDataUri()
    {
        /** @var MockObject|File $fileMock */
        $fileMock = $this->createMock(File::class);
        $fileMock->expects($this->any())
            ->method('getProperty')->with('mime_type')
            ->will($this->returnValue('jpg'));
        $fileMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue('image data'));

        /** @var MockObject|ImageUtility $imageUtilityMock */
        $imageUtilityMock = $this->getAccessibleMock(
            ImageUtility::class,
            ['getCropAreaForVariant', 'getProperty'],
            [],
            '',
            false
        );


        /** @var MockObject|ImageService $imageServiceMock */
        $imageServiceMock = $this->getAccessibleMock(
            ImageService::class,
            ['applyProcessingInstructions', 'getImageUri'],
            [],
            '',
            false
        );
        $imageServiceMock->expects($this->any())
            ->method('applyProcessingInstructions')
            ->will($this->returnValue($fileMock));

        $imageServiceMock->expects($this->any())
            ->method('getImageUri')
            ->will($this->returnValue('path/to/image.jpg'));

        $this->viewHelper->injectImageUtility($imageUtilityMock);
        $this->viewHelper->injectImageService($imageServiceMock);

        $this->viewHelper->expects($this->any())->method('renderChildren')
            ->with('cropVariant', 'string', $this->anything(), false, 'default');

        $this->viewHelper->setArguments([
            'image' => $fileMock,
            'width' => '128',
            'height' => null,
            'cropVariant' => 'default',
            'dataUri' => true,
            'absolute' => null
        ]);

        $result = $this->viewHelper->render();
        $this->assertEquals('data:jpg;base64,aW1hZ2UgZGF0YQ==', $result);
    }

    /** @test */
    public function renderReturnsRelativeUrl()
    {
        /** @var MockObject|File $fileMock */
        $fileMock = $this->createMock(File::class);
        $fileMock->expects($this->any())
            ->method('getProperty')->with('mime_type')
            ->will($this->returnValue('jpg'));
        $fileMock->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue('image data'));

        /** @var MockObject|ImageUtility $imageUtilityMock */
        $imageUtilityMock = $this->getAccessibleMock(
            ImageUtility::class,
            ['getCropAreaForVariant', 'getProperty'],
            [],
            '',
            false
        );


        /** @var MockObject|ImageService $imageServiceMock */
        $imageServiceMock = $this->getAccessibleMock(
            ImageService::class,
            ['applyProcessingInstructions', 'getImageUri'],
            [],
            '',
            false
        );
        $imageServiceMock->expects($this->any())
            ->method('applyProcessingInstructions')
            ->will($this->returnValue($fileMock));

        $imageServiceMock->expects($this->any())
            ->method('getImageUri')
            ->will($this->returnValue('path/to/image.jpg'));

        $this->viewHelper->injectImageUtility($imageUtilityMock);
        $this->viewHelper->injectImageService($imageServiceMock);

        $this->viewHelper->expects($this->any())->method('renderChildren')
            ->with('cropVariant', 'string', $this->anything(), false, 'default');

        $this->viewHelper->setArguments([
            'image' => $fileMock,
            'width' => '128',
            'height' => null,
            'cropVariant' => 'default',
            'dataUri' => false,
            'absolute' => null
        ]);

        $result = $this->viewHelper->render();
        $this->assertEquals('path/to/image.jpg', $result);
    }
}
