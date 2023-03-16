<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class PlaceholderSvgViewHelperCest extends AbstractViewHelperCest
{
    public function _before(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => ''
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
    }

    public function canSeePlaceholderSvgStringWithUncroppedImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221920%22%20height%3D%221200%22/', $placeholderBase64String);
    }

    // test if the viewhelper retrieves the correct cropVariants from the file reference as string
    public function canSeePlaceholderSvgStringWithCroppedImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":0.5,"height":0.5},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PlaceholderSvg');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%22960%22%20height%3D%22600%22%/', $placeholderBase64String);
    }

    public function canSeePlaceholderSvgStringWithNonExistingCropVariant(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&cropVariant=invalid');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221920%22%20height%3D%221200%22/', $placeholderBase64String);
    }

    // test if the viewhelper retrieves the correct cropVariants from the file reference as string
    public function canSeePlaceholderSvgStringWithCroppedImageAndAlternativeCropVariant(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PlaceholderSvg&cropVariant=mobile');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221000.32%22%20height%3D%22748.8%22%/', $placeholderBase64String);
    }

    public function canSeePlaceholderSvgStringWithAdditionalContent(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&content=additionalcontent');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221920%22%20height%3D%221200%22.*additionalcontent/', $placeholderBase64String);
    }

    public function canSeePlaceholderSvgStringWithEmbeddedPreviewImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&embedPreview=1');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221920%22%20height%3D%221200%22.*xlink%3Ahref%3D%22data%3Aimage%2Fjpeg%3Bbase64%2/', $placeholderBase64String);
    }

    public function canSeePlaceholderSvgStringWithEmbeddedPreviewImageAndEmbedPreviewWidth(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&embedPreview=1&embedPreviewWidth=2');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder');
        $I->assertRegExp('/^data:image.*width%3D%221920%22%20height%3D%221200%22.*xlink%3Ahref%3D%22data%3Aimage%2Fjpeg%3Bbase64%2/', $placeholderBase64String);
    }
}
