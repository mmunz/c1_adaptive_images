<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
class PlaceholderImageViewHelperCest extends AbstractViewHelperCest
{
    public function _before(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => ''
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
    }

    public function canSeePlaceholderImageStringWithUncroppedImage(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderImage');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder-image');
        $I->assertRegExp('/^data:image\/jpeg;base64,.+$/', $placeholderBase64String);

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(128, 80, '62.50');

        $this->validateMarkup($I);
    }

    public function canSeePlaceholderImageStringWithMobileCropVariant(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.2,"width":0.4,"x":0.2,"y":0.2},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->flushCache();

        $I->amOnPage('/index.php?mode=PlaceholderImage&cropVariant=mobile');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder-image');
        $I->assertRegExp('/^data:image\/jpeg;base64,.+$/', $placeholderBase64String);

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(128, 40, '31.25');

        $this->validateMarkup($I);
    }

    public function canSeePlaceholderImageStringWithUncroppedImageAndCustomWidth(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderImage&width=32');

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(32, 20, '62.50');

        $this->validateMarkup($I);
    }

    public function canSeePlaceholderImageStringWithUncroppedImageWithDataUriFalse(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderImage&dataUri=0');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder-image');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg$/', $placeholderBase64String);

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(128, 80, '62.50');

        $this->validateMarkup($I);
    }

    public function canSeePlaceholderImageStringWithUncroppedImageAndCustomWidthWithDataUriFalse(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderImage&width=32&dataUri=0');

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(32, 20, '62.50');

        $this->validateMarkup($I);
    }

    public function canSeePlaceholderImageStringWithUncroppedImageAndCustomWidthWithDataUriFalseAndAbsolute(\AcceptanceTester $I)
    {
        $I->flushCache();
        $I->amOnPage('/index.php?mode=PlaceholderImage&width=32&dataUri=0&absolute=1');
        $placeholderBase64String = $I->grabTextFrom('.placeholder-image');
        $I->assertRegExp('/^http:.*\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg$/', $placeholderBase64String);
    }

    public function canSeePlaceholderImageStringWithDataUriFalseAndMobileCropVariant(\AcceptanceTester $I)
    {
        $properties = [
            'crop' => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN"}, "mobile":{"cropArea":{"height":0.2,"width":0.4,"x":0.2,"y":0.2},"selectedRatio":"free"}}'
        ];
        $I->updateInDatabase('sys_file_reference', $properties, ['uid' => 1]);
        $I->flushCache();

        $I->amOnPage('/index.php?mode=PlaceholderImage&cropVariant=mobile&dataUri=0');

        $I->expect('See viewhelper output');
        $placeholderBase64String = $I->grabTextFrom('.placeholder-image');
        $I->assertRegExp('/^\/fileadmin\/_processed_\/.*\/csm_nightlife-4_.*.jpg$/', $placeholderBase64String);

        $I->waitForImagesLoaded();

        $I->expect('a small placeholder image is loaded');
        $I->seeCurrentImageDimensions(128, 40, '31.25');

        $this->validateMarkup($I);
    }
}
