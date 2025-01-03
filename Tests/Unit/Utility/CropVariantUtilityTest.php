<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Class CropVariantUtilityTest
 */
class CropVariantUtilityTest extends TestCase
{
    protected MockObject $mathUtilityMock;
    protected CropVariantUtility $utility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mathUtilityMock = $this->createMock(MathUtility::class);
        $this->utility = new CropVariantUtility($this->mathUtilityMock);
    }

    /**
     * @test
     */
    public function setCropVariantCollectionFromFileReference()
    {
        /** @var File $fileReferenceMock */
        $fileReferenceMock = $this->createMock(FileReference::class);
        $this->utility->setCropVariantCollection($fileReferenceMock);
        $this->utility->getCropAreaForVariant('default');

        Assert::assertInstanceOf(
            'TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection',
            $this->utility->getCropVariantCollection()
        );
    }

    /**
     * @test
     */
    public function setCropVariantCollectionFromFile()
    {
        /** @var File $file */
        $file = $this->createMock(File::class);
        $this->utility->setCropVariantCollection($file);

        Assert::assertInstanceOf(
            'TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection',
            $this->utility->getCropVariantCollection()
        );
    }

    /**
     * @test
     */
    public function returnsNullForFileReferenceWithoutCrop()
    {
        $fileReferenceMock = $this->getFileReferenceMock();
        $this->utility->setCropVariantCollection($fileReferenceMock);

        /** @var Area $result */
        $result = $this->utility->getCropAreaForVariant('mobile');
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function returnsNullForFile()
    {
        $file = $this->createMock(File::class);
        $this->utility->setCropVariantCollection($file);

        /** @var Area $result */
        $result = $this->utility->getCropAreaForVariant('mobile');
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function returnsCropAreaForValidKey()
    {
        $properties = [
            'width' => 1000,
            'height' => 1000,
            'crop' => '{"default":{"cropArea":{"height":1,"width":1,"x":0,"y":0}},"mobile":{"cropArea":{"height":0.5,"width":0.7,"x":0,"y":0}}}'
        ];
        $fileReferenceMock = $this->getFileReferenceMock($properties);

        $this->utility->setCropVariantCollection($fileReferenceMock);

        $expected = [
            'x' => 0,
            'y' => 0,
            'width' => 700,
            'height' => 500
        ];

        /** @var Area $result */
        $result = $this->utility->getCropAreaForVariant('mobile');
        $this->assertEquals($expected, $result->asArray());
    }

    /**
     * getFileReferenceMock
     *
     * @param array $properties
     * @return MockObject|FileReference
     */
    public function getFileReferenceMock($properties = [])
    {
        /** @var MockObject|FileReference $fileReferenceMock */
        $fileReferenceMock = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasProperty', 'getProperty'])
            ->getMock();

        $fileReferenceMock
            ->method('hasProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return true;
                }
                return false;
            }));

        $fileReferenceMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                if (array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
                return false;
            }));
        return $fileReferenceMock;
    }
}
