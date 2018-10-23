<?php

namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\Utility\Placeholder\ImagePlaceholderUtility;
use C1\AdaptiveImages\ViewHelpers\ImageViewHelper;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\Rendering\RenderingContextFixture;

/**
 * Class ImageViewHelperTest
 */
class ImageViewHelperTest extends AbstractViewHelperTest
{

    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new ImageViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
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
        $instance = $this->getAccessibleMock(ImageViewHelper::class, ['registerArgument']);
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
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @return array
     */
    public function addAdditionalAttributesProvider()
    {
        return [
            'without additionalAttributes from viewhelper' => [
                ['sizes' => '100vw', 'srcset' => ''],
                []
            ],
            'with merged additionalAttributes from viewHelper' => [
                ['sizes' => '100vw', 'srcset' => '', 'debug' => '1'],
                [
                    'additionalAttributes' => [
                        'debug' => '1'
                    ]
                ]
            ],
            'overwrite default additionalAttributes array item from viewHelper' => [
                ['sizes' => '50vw', 'srcset' => ''],
                [
                    'additionalAttributes' => ['sizes' => '50vw', 'srcset' => '']
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
        /** @var AccessibleMockObjectInterface|ImageViewHelper $imageViewHelperMock */
        $imageViewHelperMock = $this->getAccessibleMock(ImageViewHelper::class, ['getPlaceholder']);
        $imageViewHelperMock->setArguments($arguments);
        $imageViewHelperMock->_set('imagePlaceholderUtility', new ImagePlaceholderUtility());
        $imageViewHelperMock->addAdditionalAttributes();
        $this->assertEquals($expected, $imageViewHelperMock->_get('tag')->getAttributes());
    }

    /**
     * @return array
     */
    public function addDataAttributesProvider()
    {
        return [
            'without data argument from viewhelper' => [
                ['data-sizes' => 'auto', 'data-srcset' => ''],
                [
                    'data' => [],
                    'srcsetWidths' => '256,512'
                ]
            ],
            'with data argument from viewhelper' => [
                ['data-sizes' => 'auto', 'data-srcset' => '', 'data-debug' => '1'],
                [
                    'data' => [
                        'debug' => true
                    ],
                    'srcsetWidths' => '256,512'
                ]
            ],
            'overwrite default with data argument from viewhelper' => [
                ['data-sizes' => '33vw', 'data-srcset' => ''],
                [
                    'srcsetWidths' => '256,512',
                    'data' => [
                        'sizes' => '33vw'
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     * @param array $expected
     * @param array $arguments
     * @dataProvider addDataAttributesProvider
     */
    public function addDataAttributesTest($expected, $arguments)
    {
        /** @var AccessibleMockObjectInterface|ImageViewHelper $imageViewHelperMock */
        $imageViewHelperMock = $this->getAccessibleMock(ImageViewHelper::class, ['getSrcSetString']);
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
}
