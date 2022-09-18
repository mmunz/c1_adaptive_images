<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class GetSrcsetViewHelperCest extends AbstractViewHelperCest
{
    public function _before(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => ''
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
    }

    public function getSrcSetStringForImageWithoutCrop(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=GetSrcset&debug=1');

        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 400, '62.50', 0);
    }

    public function getSrcSetStringForImageWithoutCropAbsoluteUri(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=GetSrcset&absolute=1');

        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^http:.*\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,http:.*\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);
    }

    public function getSrcSetStringForImageWithInvalidCropVariant(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=GetSrcset&cropVariant=invalid&debug=1');

        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 400, '62.50', 0);
    }

    public function getSrcSetStringForImageWithoutCropAndAspectRatio(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=GetSrcset&debug=1&aspectRatio=2');

        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 320, '50.00', 0);
    }

    public function getSrcSetStringForImageWithCrop(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":0.5,"height":0.5},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->amOnPage('/index.php?mode=GetSrcset&debug=1');
        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 400, '62.50', 0);
    }

    public function getSrcSetStringForImageWithCropVariantMobile(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->amOnPage('/index.php?mode=GetSrcset&cropVariant=mobile&debug=1');
        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 479, '74.84', 0);
    }

    public function getSrcSetStringForImageWithCropAndAspectRatio(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":0.5,"height":0.5},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->amOnPage('/index.php?mode=GetSrcset&debug=1&aspectRatio=2');
        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 320, '50.00', 0);
    }

    public function getSrcSetStringForImageWithCropVariantMobileAndAspectRatio(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->amOnPage('/index.php?mode=GetSrcset&cropVariant=mobile&debug=1&aspectRatio=4.0');
        $I->expect('See viewhelper output');
        $srcSetString = $I->grabTextFrom('.srcset');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 320w,\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg 640w/', $srcSetString);

        $I->waitForImagesLoaded();
        $I->seeCurrentImageDimensions(640, 160, '25.00', 0);
    }

}
