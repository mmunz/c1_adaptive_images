<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

/**
 * Test case.
 */
abstract class AbstractViewHelperCest
{
    public function _failed(\AcceptanceTester $I)
    {
        $I->pause();
    }

    public function _before(\AcceptanceTester $I)
    {
        $I->executeConsoleCommand('configuration:set', ['-vvv', 'GFX/processor_allowUpscaling', true]);
    }

    public function validateMarkup(\AcceptanceTester $I)
    {
        /* style in head inside CDATA is htmlspecialchar'ed by webdriver which causes validation to fail, see
         * https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/4264
         * Workaround for now: ignore CSS errors in test
         * Another problem was that the validator started complaining about missing doctype while it was there.
         * So this error is ignored now, too
         */
        $I->expect('Page has valid markup.');
        $I->validateMarkup([
            'ignoredErrors' => [
                '/CSS: Parse Error./',
                '/Start tag seen without seeing a doctype first./'
            ],
        ]);
    }
}
