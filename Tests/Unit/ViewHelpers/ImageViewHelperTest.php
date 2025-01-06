<?php

namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use C1\AdaptiveImages\Utility\RatioBoxUtility;
use C1\AdaptiveImages\ViewHelpers\ImageViewHelper;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

/**
 * Class ImageViewHelperTest
 */
class ImageViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @var array
     */
    protected array $constructorArgs;

    /**
     * set up
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->viewHelper = $this->getMockBuilder(ImageViewHelper::class)->disableOriginalConstructor()->getMock();
        $imageServiceMock = $this->mockImageService();
        $imageUtility = $this->mockImageUtility();
        $cropVariantUtility = new CropVariantUtility(new MathUtility());
        $pageRendererMock = $this->createMock(PageRenderer::class);
        $ratioBoxUtility = new RatioBoxUtility($pageRendererMock, $cropVariantUtility);
        $imagePlaceHolderUtility = new ImagePlaceholderUtility($imageServiceMock, $cropVariantUtility);

        $this->constructorArgs = [
            $imageUtility,
            $ratioBoxUtility,
            $imagePlaceHolderUtility,
            $imageServiceMock
        ];

        $this->viewHelper = new ImageViewHelper(...$this->constructorArgs);
    }

    public function invalidArgumentsDataProvider(): array
    {
        return [
            [['src' => '', 'image' => null], 1382284106],
            [['src' => null, 'image' => null], 1382284106],
            [['src' => '', 'image' => null], 1382284106],
            [['src' => 'something', 'image' => 'something'], 1382284106],
            [['src' => 'something', 'image' => null, 'fileExtension' => 'dummy'], 1618989190],
        ];
    }

    /**
     * @test
     * @dataProvider invalidArgumentsDataProvider
     */
    public function renderThrowsExceptionOnInvalidArguments(array $arguments, int $expectedExceptionCode): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode($expectedExceptionCode);

        $viewHelper = new ImageViewHelper(...$this->constructorArgs);
        $viewHelper->setArguments($arguments);
        $viewHelper->render();
    }

    /**
     * @test
     */
    public function testInitializeArguments()
    {
        // key: $name of the argument
        // array: $type, $required, $default
        // description is not checked
        $rules = [
            'sources' => ['array', false, null],
            'srcsetWidths' => ['string', false, '360,768,1024,1920']
        ];

        $instance = $this->getAccessibleMock(ImageViewHelper::class, ['registerArgument'], $this->constructorArgs, '', false);
        $instance->expects($this->any())
            ->method('registerArgument')
            ->will(
                $this->returnCallback(
                    function ($name, $type, $description, $required = false, $default = null) use ($rules) {
                        if (array_key_exists($name, $rules)) {
                            $arguments = [$type, $required, $default ? $default : null];
                            $this->assertEquals($rules[$name], $arguments);
                        }
                    }
                )
            );
        $instance->initializeArguments();
    }

    public static function addAdditionalAttributesProvider(): array
    {
        return [
            'without additionalAttributes from viewhelper' => [
                ['sizes' => '100vw', 'srcset' => ''],
                ['cropVariant' => 'default']
            ],
            'with merged additionalAttributes from viewHelper' => [
                ['sizes' => '100vw', 'srcset' => '', 'debug' => '1'],
                [
                    'additionalAttributes' => [
                        'debug' => '1'
                    ],
                    'cropVariant' => 'default'
                ]
            ],
            'overwrite default additionalAttributes array item from viewHelper' => [
                ['sizes' => '50vw', 'srcset' => ''],
                [
                    'additionalAttributes' => ['sizes' => '50vw', 'srcset' => ''],
                    'cropVariant' => 'default'
                ]
            ]
        ];
    }

    /**
     * @test
     * @param array $expected
     * @param array $arguments
     * @dataProvider addAdditionalAttributesProvider
     */
    public function addAdditionalAttributesTest($expected, $arguments)
    {
        $imageViewHelperMock = $this->getAccessibleMock(ImageViewHelper::class, ['getPlaceholder', 'getSrcSetString'], $this->constructorArgs);
        $imageViewHelperMock->setArguments($arguments);
        $imageViewHelperMock->addAdditionalAttributes();
        $this->assertEquals($expected, $imageViewHelperMock->_get('tag')->getAttributes());
    }

    /**
     * @test
     * @param array $arguments
     * @param array $expected
     * @dataProvider addDataAttributesProvider
     */
    public function addDataAttributesTest($arguments, $expected)
    {
        /** @var AccessibleObjectInterface|ImageViewHelper $imageViewHelperMock */
        $imageViewHelperMock = $this->getAccessibleMock(ImageViewHelper::class, ['getSrcSetString'], $this->constructorArgs);
        $imageViewHelperMock->setArguments($arguments);
        $imageViewHelperMock->addDataAttributes();

        $viewHelperActualArguments = $imageViewHelperMock->_get('arguments');

        $viewHelperDefaultDataArgument = [
            'data' => [
                'sizes' => 'auto',
                'srcset' => $imageViewHelperMock->getSrcSetString()
            ]
        ];

        $this->assertEquals(
            array_replace_recursive($viewHelperDefaultDataArgument, $arguments),
            $viewHelperActualArguments,
            'viewHelpers $arguments not set/merged properly.'
        );

        $this->assertEquals(
            $expected,
            $imageViewHelperMock->_get('tag')->getAttributes(),
            'data attributes not properly added to tag'
        );
    }

    public static function addDataAttributesProvider(): array
    {
        return [
            'without data argument from viewhelper' => [
                [
                    'lazy' => true,
                    'data' => [],
                    'srcsetWidths' => '256,512'
                ],
                ['data-sizes' => 'auto', 'data-srcset' => ''],
            ],
            'with data argument from viewhelper' => [
                [
                    'lazy' => true,
                    'data' => [
                        'debug' => '1'
                    ],
                    'srcsetWidths' => '256,512'
                ],
                ['data-sizes' => 'auto', 'data-srcset' => '', 'data-debug' => '1'],
            ],
            'overwrite default with data argument from viewhelper' => [
                [
                    'lazy' => true,
                    'srcsetWidths' => '256,512',
                    'data' => [
                        'sizes' => '33vw'
                    ]
                ],
                ['data-sizes' => '33vw', 'data-srcset' => ''],
            ],
        ];
    }

    public static function isLazyLoadingProvider(): array
    {
        return [
            'no lazy argument' => [
                false,
                [],
            ],
            'with lazy = false' => [
                false,
                ['lazy' => false],
            ],
            'with lazy = true' => [
                true,
                ['lazy' => true]
            ],
        ];
    }
    /**
     * @test
     * @param array $expected
     * @param array $arguments
     * @dataProvider isLazyLoadingProvider
     */
    public function isLazyLoadingTest($expected, $arguments)
    {
        $this->viewHelper->setArguments($arguments);
        $this->assertEquals($expected, $this->viewHelper->isLazyLoading());
    }
}
