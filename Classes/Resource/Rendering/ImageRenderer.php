<?php

namespace C1\ImageRenderer\Resource\Rendering;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use C1\ImageRenderer\Utility\ImageUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;



class ImageRenderer implements FileRendererInterface
{

    /**
     * @var array
     */
    protected $possibleMimeTypes = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * var mixed
     */
    protected $settings = null;

    /**
     * var mixed
     */
    protected $viewConfiguration = null;


    /** var array */
    protected $options;

    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $file;

    /**
     * @var FileInterface
     */
    protected $originalFile;

    /**
     * @var ImageUtility
     */
    protected $imageUtility;



    /**
     * Constructor
     */
    public function __construct($configuration = null, $objectManager = null)
    {
        if (!$objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        } else {
            $this->objectManager = $objectManager;
        }
        $this->imageUtility = $this->objectManager->get(ImageUtility::class);



//        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
//        $this->utility = $this->objectManager->get(ImageRendererUtility::class);

//        if (!$configuration) {
//            $this->configuration = $this->objectManager->get(ImageRendererConfiguration::class);
//        } else {
//            $this->configuration = $configuration;
//        }
//        $this->settings = $this->configuration->getSettings();
//        $this->view = $this->configuration->getView();
    }




    /**
     * @return int
     */
    public function getPriority()
    {
        return 5;
    }

    /**
     * @param FileInterface $file
     * @return bool
     */
    public function canRender(FileInterface $file)
    {
        return in_array($file->getMimeType(), $this->possibleMimeTypes, true);
    }

    /*
     * Get the cObj which is the parent
     */
//    public function getContentObject() {
//
//    }


    /**
     * @param array $options
     * @return void
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @param $file FileInterface
     * @return $this
     */
    public function setFile($file)
    {
        $this->originalFile = $file;

        if ($file instanceof FileReference) {
            $this->file = $file->getOriginalFile();
            return $this;
        }
        $this->file = $file;
        return $this;
    }

    public function renderFluidTemplate() {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $viewTmpl */
        $viewTmpl = $this->objectManager->get(StandaloneView::class);
        $viewTmpl->setLayoutRootPaths($this->viewConfiguration['layoutRootPaths']);
        $viewTmpl->setTemplateRootPaths($this->viewConfiguration['templateRootPaths']);
        $viewTmpl->setPartialRootPaths($this->viewConfiguration['partialRootPaths']);
        $viewTmpl->setTemplate('ImageRenderer');
        //$viewTmpl->assign('this', $this);
        $viewTmpl->assign('settings', $this->settings);
        $viewTmpl->assign('options', $this->options);
        $viewTmpl->assign('sources', $this->imageUtility->getCropVariants());
        $viewTmpl->assign('defaultImage', $this->imageUtility->getDefaultImage());

        return $viewTmpl->render();
    }

    /**
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(
        FileInterface $file,
        $width,
        $height,
        array $options = array(),
        $usedPathsRelativeToCurrentScript = false
    ) {
        $this->setFile($file);
        $this->setOptions($options);
        $this->imageUtility->setOriginalFile($file);
        $this->imageUtility->setOptions($this->options);


        $pluginSettingsService = $this->objectManager->get('C1\\ImageRenderer\\Service\\SettingsService');
        $this->settings = $pluginSettingsService->getSettings();

        $this->viewConfiguration = $pluginSettingsService->getViewConfiguration();

        return $this->renderFluidTemplate();


//        $this->init($width, $height, $options);
//        if ($options['renderType'] === 'adaptive') {
//            $this->createSrcSet();
//            return $this->buildAll();
//        } else if ($options['renderType'] === 'fluidtemplate') {
//            $this->createSrcSet();
//            return $this->renderFluidTemplate();
//        } else {
//            return $this->buildSimpleImgTag();
//        }
    }

}