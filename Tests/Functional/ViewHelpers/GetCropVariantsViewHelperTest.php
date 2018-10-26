<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Functional\ViewHelpers;

/**
 * Class GetCropVariantsViewHelperTest
 *
 */
class GetCropVariantsViewHelperTest extends AbstractFunctionalTest
{

    /**
     * @test
     *
     * test if the viewhelper retrieves the correct cropVariants from the file reference as string
     */
    public function canReturnCropVariantsAsString()
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}'
        ];
        $this->createAndReturnFileReference('user_upload/nightlife-4.jpg', 'tt_content', 'assets', 1, 0, $properties);
        // @ToDo - can only pass strings for now, find out how to pass arrays
        // $arguments = '&mq-mobile=(max-width:680px)';
        $arguments = '&mode=GetCropVariantsAsString';
        $response = $this->getFrontendResponse($this->pageId, 0, 0, 0, true, 0, $arguments);

        $expected = '<pre class="cropString">{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":null,"focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}</pre>';
        $this::assertContains($expected, $response->getContent());
    }
}
