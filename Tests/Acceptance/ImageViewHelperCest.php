<?php
declare(strict_types = 1);
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

        $I->amOnPage('/index.php?mode=ImageViewHelper&placeholderWidth=128&srcsetWidths=640,1024&debug=1');
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
}
