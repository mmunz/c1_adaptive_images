<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Module\WebDriver;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class Acceptance extends Module
{
    /** @var WebDriver */
    protected $webdriver;

    /**
     * Module constructor.
     *
     * Requires module container (to provide access between modules of suite) and config.
     *
     * @param ModuleContainer $moduleContainer
     * @param null $config
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->webdriver = $this->getModule('WebDriver');
    }

    public function restartBrowser()
    {
        $this->webdriver->_restart();
    }

    public function changeBrowser($browser)
    {
        $this->webdriver->_restart(['browser' => $browser]);
    }

    public function resize($width, $height)
    {
        $this->webdriver->resizeWindow($width, $height);
        $size = $this->webdriver->executeJS('window.innerWidth');

        // chrome and chromedriver 108 had problems with resizing sometimes
        // this seems to fix it
        if ($size !== $width) {
            $this->webdriver->wait(1);
            $this->webdriver->resizeWindow($width, $height);
        }
    }

    /** getCurrentImage
     *
     *  return the currently loaded image for the image element at index
     *
     * @param int $index 0: first image, 1: second image, ...
     * @return array
     */
    public function getCurrentImage($index = 0)
    {
        $img = $this->webdriver->executeJS("var img=document.querySelectorAll('img')[" . $index . '];return getImageDimensions(img)');
        return $img;
    }

    /** seeCurrentImageDimensions
     *
     *  check if the dimension of the currently loaded image match the expected dimensions
     *
     * @param int $width
     * @param int $height
     * @param string $ratio
     * @param int $index 0: first image, 1: second image, ...
     * @return void
     */
    public function seeCurrentImageDimensions(int $width, int $height, string $ratio, int $index = 0)
    {
        $dimensions = [
            'width' => $width,
            'height' => $height,
            'ratio' => $ratio
        ];
        $img = $this->getCurrentImage($index);
        $this->assertEquals($dimensions['width'], $img['width']);
        $this->assertEquals($dimensions['height'], $img['height']);
        $this->assertEquals($dimensions['ratio'], $img['ratio']);
    }

    public function initLazySizes()
    {
        $this->webdriver->executeJS('lazySizes.init();');
    }

    public function waitForImagesLoaded()
    {
        // @ToDo remove wait, use js callback or event
        $this->webdriver->wait(0.5);
        //        $this->webdriver->waitForJS(
        //            'return document.readyState == "complete"',
        //            10
        //        );
    }

    /** getJsDebug
     *
     *  get the debug tag inserted by javascript
     *
     * @param int $index 0: debug tag for the first image, 1: second image, ...
     * @return array
     */
    public function getJsDebug($index = 0)
    {
        $debugMarkup = $this->webdriver->executeJS("try {return document.querySelectorAll('.img-debug')[" . $index . '].innerHTML} catch {return null}');
        return $debugMarkup;
    }

    /** seeJsDebug
     *
     *  check if the debug tag was inserted using javascript
     *
     * @param int $index 0: debug tag for the first image, 1: second image, ...
     * @return array
     */
    public function seeJsDebug($index = 0)
    {
        $this->assertRegexp('/.*640x400.*(62.50).*container: \d.*/', $this->getJsDebug($index));
    }

    /** dontSeeJsDebug
     *
     *  true if no debug tag was inserted using javascript
     *
     * @param int $index 0: debug tag for the first image, 1: second image, ...
     * @return array
     */
    public function cantSeeJsDebug($index = 0)
    {
        $this->assertEquals(null, $this->getJsDebug($index));
    }

    /**
     * See if padding bottom value on a ratio box matches an expected value
     *
     * @param int $index 0: use the first ratio box, 1: use second, ...
     * @param string $class: css selector (e.g. 'rb')
     * @param string $expectedPaddingBottom expected padding bottom value (e.g. '75%')
     */
    public function seeRatioBoxHasPaddingBottom($index, $class, $expectedPaddingBottom)
    {
        $actualPaddingBottom = $ratioBoxStyle = $this->webdriver->executeJS(
            "function getDefaultStyle(element, prop) {
                var parent = element.parentNode,
                    computedStyle = getComputedStyle(element),
                    value;
                parent.style.display = 'none';
                value = computedStyle.getPropertyValue(prop);
                parent.style.removeProperty('display');
                return value;
            }
            try {
                return getDefaultStyle(document.querySelectorAll('" . $class . "')[" . $index . "], 'padding-bottom')
            }
            catch {return null}"
        );
        $this->assertEquals($expectedPaddingBottom, $actualPaddingBottom);
    }

    public function executeConsoleCommand(string $command, array $args = [], $env = []): array
    {
        $escapedArgs = array_map('escapeshellarg', $args);
        $cmd = PHP_BINARY . ' .Build/vendor/bin/typo3 ' . $command;
        foreach ($escapedArgs as $arg) {
            $cmd .= ' ' . $arg;
        }

        $envVars = '';
        foreach ($env as $key => $value) {
            $envVars .= ' ' . $key . '=' . $value;
        }

        $cmd = \ltrim($envVars) . ' ' . $cmd;

        $this->debugSection('Command', $cmd);
        $output = '';

        $handle = popen($cmd, 'r');
        while (!feof($handle)) {
            $output .= fgets($handle, 4096);
        }
        $status = pclose($handle);

        $this->debugSection('Status', $status);
        $this->debugSection('Output', $output);

        return [
            'status' => $status,
            'output' => $output,
        ];
    }

    /**
     * @param string $statement
     * @throws \JsonException
     */
    public function executeInDatabase(string $statement): void
    {
        $builder = new Process(['.Build/vendor/bin/typo3', 'database:import']);
        $input = new InputStream();
        $builder->setInput($input);
        $input->write($statement);
        $this->debugSection('Execute', $builder->getCommandLine());
        $builder->start();
        $input->close();
        $builder->wait();
    }

    public function flushCache()
    {
        $this->executeConsoleCommand('cache:flush');
    }
}
