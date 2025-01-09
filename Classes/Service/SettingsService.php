<?php
declare(strict_types=1);

namespace C1\AdaptiveImages\Service;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class SettingsService
{
    protected ?array $frameworkConfiguration = null;
    protected ?array $settings = null;

    public function __construct(
        private readonly ConfigurationManager $configurationManager
    ) {
    }

    /**
     * Returns the framework configuration.
     */
    public function getFrameworkConfiguration(): array
    {
        if ($this->frameworkConfiguration === null) {
            $this->frameworkConfiguration = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'c1_adaptive_images'
            );
        }
        return $this->frameworkConfiguration;
    }

    /**
     * Returns the TypoScript array at path $path, which is separated by ".",
     * e.g. "settings.foo".
     * "settings.foo" would return $this->frameworkConfiguration['settings']['foo'].
     *
     * If the path is invalid or no entry is found, false is returned.
     */
    public function getByPath(string $path): mixed
    {
        return ObjectAccess::getPropertyPath($this->getFrameworkConfiguration(), $path);
    }

    /**
     * Returns all TypoScript settings.
     */
    public function getSettings(): array
    {
        if ($this->settings === null) {
            $this->settings = $this->getByPath('settings');
        }
        return $this->settings;
    }
}
