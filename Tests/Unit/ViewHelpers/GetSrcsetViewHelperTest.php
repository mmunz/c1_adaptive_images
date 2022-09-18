<?php

namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\DebugUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use C1\AdaptiveImages\ViewHelpers\GetSrcsetViewHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;

class GetSrcsetViewHelperTest extends TestCase
{
    public function testInitializeArguments()
    {
        $mathUtility = new MathUtility();
        $mathUtility->setLogger(new NullLogger());
        $viewHelper = new GetSrcsetViewHelper(
            $this->createMock(ImageService::class),
            $mathUtility,
            new DebugUtility()
        );

        $arguments = $viewHelper->prepareArguments();

        self::assertCount(6, $arguments);

        $widthArgument = $arguments['widths'];

        self::assertEquals('string', $widthArgument->getType());
        self::assertEquals(false, $widthArgument->isRequired());
        self::assertEquals([320, 640, 1024, 1440, 1920], $widthArgument->getDefaultValue());

        $cropVariantArgument = $arguments['cropVariant'];

        self::assertEquals('string', $cropVariantArgument->getType());
        self::assertEquals(false, $cropVariantArgument->isRequired());
        self::assertEquals('default', $cropVariantArgument->getDefaultValue());
    }

    /** @dataProvider createSrcsetStringProvider */
    public function testCreateSrcsetString(array $arguments, string $expected)
    {
        $file = $this->createMock(FileInterface::class);

        $arguments = array_merge(
            $arguments,
            [
                'cropVariant' => 'default',
                'debug' => false,
                'absolute' => false,
                'file' => $file
            ]
        );

        $imageService = $this->createMock(ImageService::class);
        $imageService
            ->expects($this->any())
            ->method('applyProcessingInstructions')
            ->with($file)
            ->willReturnCallback(function ($file, $configuration) {
                $processedFile = $this->createMock(ProcessedFile::class);
                $processedFile->method('getProperty')->willReturn(rtrim($configuration['width'], 'm'));
                return $processedFile;
            });

        $imageService
            ->expects($this->any())
            ->method('getImageUri')
            ->willReturnCallback(function ($image, $absolute) {
                return (($absolute) ? 'http://domain.tld/' : '') . 'image@' . $image->getProperty('width') . '.jpg';
            });

        $viewHelper = new GetSrcsetViewHelper(
            $imageService,
            new MathUtility(),
            new DebugUtility()
        );
        $viewHelper->setArguments($arguments);
        $result = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($expected, $result);
    }

    public function createSrcsetStringProvider(): array
    {
        return [
            'with 1 srcset width' => [
                [
                    'widths' => '240',
                ],
                'image@240.jpg 240w',
            ],
            // If only one width is given in the view helpers arguments then it might be converted to integer.
            'with 1 srcset width given as int' => [
                [
                    'widths' => 240,
                ],
                'image@240.jpg 240w',
            ],
            'with 3 srcset widths' => [
                [
                    'widths' => '240,320,480',
                ],
                'image@240.jpg 240w,image@320.jpg 320w,image@480.jpg 480w',
            ],
        ];
    }
}
