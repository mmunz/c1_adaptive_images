<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class PictureViewHelperCest extends AbstractViewHelperCest
{
    public function seePictureLoadInCorrectDimensions(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&srcsetWidths=640,1024&debug=1&lazy=0');
        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors but close to 4:3.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded with 16:9 ratio.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seePictureLoadInCorrectDimensionsWithRatioBox(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->restartBrowser();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&srcsetWidths=640,1024&debug=1&lazy=0&ratiobox=1');

        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors but close to 4:3.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--max-width767px-74dot86', '74.86%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded with 16:9 ratio.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');
    }

    public function seePictureLoadInCorrectDimensionsWithMultipleSourcesAndRatioBox(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->restartBrowser();

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}, "tablet":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelperWithMultipleSources&srcsetWidths=640,1024&debug=1&lazy=0&ratiobox=1');
        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors but close to 4:3.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--max-width767px-74dot86', '74.86%');

        $I->resizeWindow(992, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 992px image is loaded with 4:3 ratio.');
        $I->seeCurrentImageDimensions(992, 743, '74.90');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--min-width768pxandmax-width992px-74dot86', '74.86%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded with 16:9 ratio.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');
    }

    public function seePictureLoadInCorrectDimensionsWithLazySizesAndImagePlaceholder(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1&lazy=1');
        $this->validateMarkup($I);

        $I->expect('a placeholder image in mobile format (4:3 aspect ratio) is loaded');
        $I->seeCurrentImageDimensions(128, 96, '75.00');

        $I->initLazySizes();

        $I->expect('Page still has valid markup.');
        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seePictureLoadInCorrectDimensionsWithLazySizesAndImagePlaceholderInHalfWidth(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->restartBrowser();

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1&lazy=1&containerWidth=50%25');
        $this->validateMarkup($I);

        $I->expect('a placeholder image in mobile format (4:3 aspect ratio) is loaded');
        $I->seeCurrentImageDimensions(128, 96, '75.00');

        $I->initLazySizes();

        $I->expect('Page still has valid markup.');
        $this->validateMarkup($I);

        $I->expect('a 320px image is loaded with 4:3 ratio.');
        $I->seeCurrentImageDimensions(320, 240, '75.00');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 640px image is loaded in 16:9 ratio.');
        $I->seeCurrentImageDimensions(640, 400, '62.50');
    }

    public function seeCorrectRatioClassWithTwoImages(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->restartBrowser();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.2,"width":0.4,"x":0.2,"y":0.2},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 2]);
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 3]);

        $I->amOnPage('/index.php?id=2&mode=PictureViewHelper&srcsetWidths=640,1024&debug=1&lazy=0&ratiobox=1');

        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded.');
        $I->seeCurrentImageDimensions(640, 200, '31.25', 0);
        $I->seeCurrentImageDimensions(640, 479, '74.84', 1);

        $I->seeRatioBoxHasPaddingBottom(0, '.rb--max-width767px-31dot25', '31.25%');
        // second image. still index=0:seeCorrectRatioClassWithTwoImages because this is the only image with this class name
        $I->seeRatioBoxHasPaddingBottom(0, '.rb--max-width767px-74dot86', '74.86%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50', 0);
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');
        $I->seeRatioBoxHasPaddingBottom(1, '.rb.rb--62dot5', '62.5%');
    }

    public function seePictureLoadInCorrectDimensionsWhenUpscaleIsEnabled(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&srcsetWidths=640,2560&debug=1&lazy=0');
        $this->validateMarkup($I);

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('An upscaled 2560px image is loaded');
        $I->seeCurrentImageDimensions(2560, 1600, '62.50');
    }

    public function seePictureLoadInCorrectDimensionsWhenUpscaleIsDisabled(\AcceptanceTester $I)
    {
        $I->executeCommand('configuration:set', ['-vvv', 'GFX/processor_allowUpscaling', false]);
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&srcsetWidths=640,2560&debug=1&lazy=0');
        $this->validateMarkup($I);

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('The image is loaded in the sources max size which is 1920px width.');
        $I->seeCurrentImageDimensions(1920, 1200, '62.50');
    }

    public function laterCssClassesDoNotOverwritePreviousWithMediaQuery(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->restartBrowser();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.2,"width":0.4,"x":0.2,"y":0.2},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 2]);
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":0.75},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.2,"width":0.4,"x":0.2,"y":0.2},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 3]);

        $I->amOnPage('/index.php?id=2&mode=PictureViewHelper&srcsetWidths=640,1024&debug=1&lazy=0&ratiobox=1');

        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded.');
        $I->seeCurrentImageDimensions(640, 200, '31.25', 0);
        $I->seeCurrentImageDimensions(640, 200, '31.25', 1);

        $I->seeRatioBoxHasPaddingBottom(0, '.rb--max-width767px-31dot25', '31.25%');
        $I->seeRatioBoxHasPaddingBottom(1, '.rb--max-width767px-31dot25', '31.25%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50', 0);
        $I->seeCurrentImageDimensions(1024, 480, '46.88', 1);
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');
        // still index = 0 because it's the only image with this class on this page
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--46dot88', '46.88%');
    }

    public function seePictureLoadInCorrectDimensionsForNonDefaultVariant(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}, "notDefault":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->restartBrowser();
        $I->amOnPage('/index.php?mode=PictureViewHelperDifferentDefaultCropVariant&srcsetWidths=640,1024&debug=1&lazy=0');
        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors but close to 4:3.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded with 16:9 ratio.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seePictureLoadInCorrectDimensionsForNonDefaultVariantWithRatioBoxAndLazy(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}, "notDefault":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->restartBrowser();
        $I->amOnPage('/index.php?mode=PictureViewHelperDifferentDefaultCropVariant&srcsetWidths=640,1024&debug=1&lazy=1&ratiobox=1');
        $this->validateMarkup($I);

        $I->expect('a placeholder image in mobile format (4:3 aspect ratio) is loaded');
        $I->seeCurrentImageDimensions(32, 24, '75.00');

        $I->initLazySizes();

        $I->expect('a 640px image is loaded. Ratio is odd because of rounding errors but close to 4:3.');
        $I->seeCurrentImageDimensions(640, 479, '74.84');

        $I->seeRatioBoxHasPaddingBottom(0, '.rb--max-width767px-74dot86', '74.86%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();

        $I->expect('a 1024px image is loaded with 16:9 ratio.');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');

        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');
    }
}
