<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class ImageViewHelperCest extends AbstractViewHelperCest
{
    public function testUpdateDatabase(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
    }

    public function seeImageLoadInCorrectDimensions(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&srcsetWidths=640,1024&debug=1&lazy=1');
        $this->validateMarkup($I);

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(32, 20, '62.50');

        $I->initLazySizes();
        $I->waitForImagesLoaded();
        //$I->wait(5);
        $I->expect('Page still has valid markup.');
        $this->validateMarkup($I);

        $I->expect('a 640px image is loaded');
        $I->seeCurrentImageDimensions(640, 400, '62.50');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');
    }

    public function seePlaceholderWithCustomWidth(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&srcsetWidths=640,1024&placeholderWidth=16&debug=1&lazy=1');
        $this->validateMarkup($I);

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(16, 10, '62.50');
    }

    public function seeImageWithoutLazyLoading(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=32&srcsetWidths=640,1024&debug=1&lazy=0');

        $this->validateMarkup($I);
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

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=32&srcsetWidths=640,1024&debug=1&lazy=1&ratiobox=1');

        $this->validateMarkup($I);

        $I->initLazySizes();
        $I->waitForImagesLoaded();

        $I->expect('a 640px image is loaded');
        $I->seeCurrentImageDimensions(640, 400, '62.50');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');

        $I->resizeWindow(1024, 768);
        $I->waitForImagesLoaded();
        $I->expect('a 1024px image is loaded');
        $I->seeCurrentImageDimensions(1024, 640, '62.50');

        $I->expect('ratio box style in header');
        $I->seeInPageSource('.rb--62dot5{padding-bottom:62.5%}');
        $I->seeRatioBoxHasPaddingBottom(0, '.rb.rb--62dot5', '62.5%');

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

    public function canSeeUpscaledImageWhenUpscaleIsEnabled(\AcceptanceTester $I)
    {
        $I->restartBrowser();
        $I->flushCache();

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1.0,"height":1.0},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?debug=1&mode=ImageViewHelper&srcsetWidths=360,2560');
        $I->expect('The image which is 1920px source size is upscaled to 2560px.');
        $I->seeCurrentImageDimensions(2560, 1600, '62.50');
    }

    public function cantSeeUpscaledImageWhenUpscaleIsDisabled(\AcceptanceTester $I)
    {
        $I->executeCommand('configuration:set', ['-vvv', 'GFX/processor_allowUpscaling', false]);
        $I->wait(1);
        $I->flushCache();
        $I->wait(1);
        $I->restartBrowser();

        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1.0,"height":1.0},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);

        $I->amOnPage('/index.php?debug=1&mode=ImageViewHelper&srcsetWidths=360,2560');
        $I->expect('The image which is 1920px is not upscaled to 2560px.');
        $I->seeCurrentImageDimensions(1920, 1200, '62.50');
    }

    public function seeNoExceptionOnMissingImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 4]);

        $I->amOnPage('/index.php?id=3&mode=ImageViewHelper&placeholderWidth=32&srcsetWidths=640,1024&debug=1&lazy=1');
        $this->validateMarkup($I);

        $I->expect('No exception is thrown on missing image');
        $I->dontSeeInSource('Call to undefined method TYPO3\CMS\Core\Resource\ProcessedFile::setMissing()');
    }

    public function seeNoExceptionOnEmptyImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?id=4&mode=ImageViewHelper&placeholderWidth=32&srcsetWidths=640,1024&debug=1&lazy=1&ratiobox=1');
        $this->validateMarkup($I);

        $I->expect('Don\'t see exception');
        $I->dontSeeInSource('Division by zero');

        $I->expect('Ratio box class is rb--0');
        $I->waitForElement('div.rb.rb--0');
    }
}
