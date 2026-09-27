<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Service\SettingsService;
use C1\AdaptiveImages\Utility\CropVariantUtility;
use C1\AdaptiveImages\Utility\DebugUtility;
use C1\AdaptiveImages\Utility\ImageUtility;
use C1\AdaptiveImages\Utility\MathUtility;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageUtilityTest extends TestCase
{
    private function getImageUtility(): ImageUtility
    {
        return new ImageUtility(
            self::createStub(SettingsService::class),
            self::createStub(ImageService::class),
            self::createStub(CropVariantUtility::class),
            self::createStub(DebugUtility::class),
            self::createStub(MathUtility::class),
            null,
            ['srcsetWidths' => '320,640']
        );
    }

    #[Test]
    public function getSrcSetStringListsEachCandidate(): void
    {
        self::assertSame(
            'a.webp 320w,b.webp 640w',
            $this->getImageUtility()->getSrcSetString([
                320 => ['url' => 'a.webp', 'width' => 320],
                640 => ['url' => 'b.webp', 'width' => 640],
            ])
        );
    }

    #[Test]
    public function getSrcSetStringSkipsCandidatesWithAWidthAlreadyListed(): void
    {
        // Image smaller than the requested widths 480 and 640: both end up at 365px
        self::assertSame(
            'a.webp 320w,b.webp 365w',
            $this->getImageUtility()->getSrcSetString([
                320 => ['url' => 'a.webp', 'width' => 320],
                480 => ['url' => 'b.webp', 'width' => 365],
                640 => ['url' => 'c.webp', 'width' => 365],
            ])
        );
    }
}
