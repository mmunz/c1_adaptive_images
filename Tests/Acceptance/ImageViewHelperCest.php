<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class ImageViewHelperCest
{
    public function _failed(\AcceptanceTester $I)
    {
        $I->pauseExecution();
    }

    public function seeImageLoadInCorrectDimensions(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1&lazy=1');
        $I->expect('Page has valid markup.');
        $I->validateMarkup();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(128, 80, '62.50');

        $I->initLazySizes();
        //$I->wait(5);
        $I->expect('Page still has valid markup.');
        $I->validateMarkup();

        $I->expect('a 640px image is loaded');
        $I->seeCurrentImageDimensions(640, 400, '62.50');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seeImageWithoutLazyLoading(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1&lazy=0');

        $I->expect('Page has valid markup.');
        $I->validateMarkup();
        $I->expect('a 640px image is loaded');
        $I->seeCurrentImageDimensions(640, 400, '62.50');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seeLazyImageWithRatioBox(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1&lazy=1&ratiobox=1');

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

        $I->initLazySizes();

        $I->expect('a 640px image is loaded');
        $I->seeCurrentImageDimensions(640, 400, '62.50');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');

        $I->expect('ratio box style in header');
        $I->seeInPageSource('.rb--62dot5{padding-bottom:62.5%}');

        //$webdriver->findElement(WebDriverBy::cssSelector('#t3-login-submit'))->getCSSValue('background-color');

        $I->expect('ratio box wrapper exists and has correct classes');
        $I->seeElement('div.rb.rb--62dot5');
    }

    public function canSwitchJsDebugOutput(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1.0,"height":1.0},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&srcsetWidths=640');
        $I->expect('Default: Loaded without jsdebug, can\'t see debug output added by javascript.');
        $I->cantSeeJsDebug();

        $I->flushCache();
        $I->amOnPage('/index.php?mode=ImageViewHelper&srcsetWidths=640&jsdebug=0');
        $I->expect('jsdebug disabled, can\'t see debug output added by javascript.');
        $I->cantSeeJsDebug();

        $I->flushCache();
        $I->amOnPage('/index.php?mode=ImageViewHelper&jsdebug=1&srcsetWidths=640');
        $I->expect('can see debug output added by javascript');
        $I->seeJsDebug();
    }
}
