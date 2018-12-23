<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class PictureViewHelperCest
{
    public function _failed(\AcceptanceTester $I)
    {
        $I->pauseExecution();
    }

    public function seePictureLoadInCorrectDimensions(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.624,"width":0.521,"x":0,"y":0},"selectedRatio":"4:3"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=PictureViewHelper&srcsetWidths=640,1024&debug=1&lazy=0');

        $I->expect('Page has valid markup.');
        $I->validateMarkup();

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

        $I->expect('Page has valid markup.');
        /* style in head inside CDATA is htmlspecialchar'ed by webdriver which causes validation to fail, see
         * https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/4264
         * Workaround for now: ignore CSS errors in test
         */
        $I->validateMarkup([
            'ignoredErrors' => [
                '/CSS: Parse Error./',
            ],
        ]);

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

        $I->expect('Page has valid markup.');
        /* style in head inside CDATA is htmlspecialchar'ed by webdriver which causes validation to fail, see
         * https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/4264
         * Workaround for now: ignore CSS errors in test
         */
        $I->validateMarkup([
            'ignoredErrors' => [
                '/CSS: Parse Error./',
            ],
        ]);

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
        $I->expect('Page has valid markup.');

        $I->validateMarkup();

        $I->expect('a placeholder image in mobile format (4:3 aspect ratio) is loaded');
        $I->seeCurrentImageDimensions(128, 96, '75.00');

        $I->initLazySizes();

        $I->expect('Page still has valid markup.');
        $I->validateMarkup();

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
        $I->expect('Page has valid markup.');

        $I->validateMarkup();

        $I->expect('a placeholder image in mobile format (4:3 aspect ratio) is loaded');
        $I->seeCurrentImageDimensions(128, 96, '75.0');

        $I->initLazySizes();

        $I->expect('Page still has valid markup.');
        $I->validateMarkup();

        $I->expect('a 320px image is loaded with 4:3 ratio.');
        $I->seeCurrentImageDimensions(320, 240, '75.00');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 640px image is loaded in 16:9 ratio.');
        $I->seeCurrentImageDimensions(640, 400, '62.50');
    }
}
