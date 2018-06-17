<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Service;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class SettingsService
 * @package C1\AdaptiveImages\Service
 *
 * Get TypoScript settings and view configuration.
 *
 */
class SettingsService
{
    /**
     * @var mixed
     */
    protected $frameworkConfiguration = null;

    /**
     * @var mixed
     */
    protected $settings = null;

    /**
     * @var mixed
     */
    protected $viewConfiguration = null;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Injects the Configuration Manager and loads the settings
     *
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Returns the framework configuration.
     *
     * @return array
     */
    public function getFrameworkConfiguration()
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
     *
     * @param string $path
     * @return mixed
     */
    public function getByPath($path)
    {
        return ObjectAccess::getPropertyPath($this->getFrameworkConfiguration(), $path);
    }

    /**
     * Returns all TypoScript settings.
     *
     * @return array
     */
    public function getSettings()
    {
        if ($this->settings === null) {
            $this->settings = $this->getByPath('settings');
        }
        return $this->settings;
    }

    /**
     * Returns the view configuration from TypoScript.
     *
     * @return array
     */
    public function getViewConfiguration()
    {
        if ($this->viewConfiguration === null) {
            $this->viewConfiguration = $this->getByPath('view');
        }
        return $this->viewConfiguration;
    }
}
