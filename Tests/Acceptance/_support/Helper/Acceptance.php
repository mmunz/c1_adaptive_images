<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Lib\ModuleContainer;
use Codeception\Module\WebDriver;

class Acceptance extends \Codeception\Module
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
        $this->assertArraySubset($dimensions, $img);
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
}
