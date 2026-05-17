<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Tests\Acceptance;

use AcceptanceTester;

/**
 * Test case.
 */
abstract class AbstractViewHelperCest
{
    public function _failed(AcceptanceTester $I)
    {
        $I->pause();
    }

    public function _before(AcceptanceTester $I)
    {
        $I->executeConsoleCommand('configuration:set', ['-vvv', 'GFX/processor_allowUpscaling', true]);
    }

    public function validateMarkup(AcceptanceTester $I)
    {
        $I->expect('Page has valid markup.');
        // Selenium's getPageSource() does not include the DOCTYPE declaration,
        // so we suppress the resulting false positive.
        $I->validateMarkup([
            'ignoredErrors' => [
                '/No DOCTYPE specified\./',
            ],
        ]);
    }
}
