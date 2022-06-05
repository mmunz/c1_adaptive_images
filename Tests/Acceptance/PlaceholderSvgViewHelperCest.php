<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class PlaceholderSvgViewHelperCest extends AbstractViewHelperCest
{
    public function canSeePlaceholderSvgStringWithUncroppedImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg');

        $I->expect('See viewhelper output');
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221920%22%20height%3D%221200%22%20%2F%3E');
    }

    public function _before(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => ''
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
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
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22960%22%20height%3D%22600%22%20%2F%3E');
    }

    public function canSeePlaceholderSvgStringWithNonExistingCropVariant(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&cropVariant=invalid');

        $I->expect('See viewhelper output');
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221920%22%20height%3D%221200%22%20%2F%3E');
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
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221000.32%22%20height%3D%22748.8%22%20%2F%3E');
    }

    public function canSeePlaceholderSvgStringWithAdditionalContent(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&content=additionalcontent');

        $I->expect('See viewhelper output');
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221920%22%20height%3D%221200%22%3Eadditionalcontent%3C%2Fsvg%3E');
    }

    public function canSeePlaceholderSvgStringWithEmbeddedPreviewImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&embedPreview=1');

        $I->expect('See viewhelper output');
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221920%22%20height%3D%221200%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%3E%3Cimage%20preserveAspectRatio');
    }

    public function canSeePlaceholderSvgStringWithEmbeddedPreviewImageAndEmbedPreviewWidth(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderSvg&embedPreview=1&embedPreviewWidth=2');

        $I->expect('See viewhelper output');
        $I->seeInSource('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221920%22%20height%3D%221200%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%3E%3Cimage%20preserveAspectRatio');
    }


}



