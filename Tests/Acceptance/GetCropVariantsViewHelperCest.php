<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class GetCropVariantsViewHelperCest extends AbstractViewHelperCest
{
    // test if the viewhelper retrieves the correct cropVariants from the file reference as string
    public function canReturnCropVariantsAsString(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=GetCropVariantsAsString');

        $I->expect('See viewhelper output');
        $I->seeInSource('{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":null,"focusArea":{"x":0.3333333333333333,"y":0.3333333333333333,"width":0.3333333333333333,"height":0.3333333333333333}}}');
    }
}
