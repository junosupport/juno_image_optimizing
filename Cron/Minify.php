<?php
/**
 * @Author: Hieu Nguyen <hieu@junowebdesign.com>
 */

namespace Juno\Minify\Cron;

use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;

class Minify
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $config,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $config;
        $this->directoryList = $directoryList;
        $this->_storeManager = $storeManager;
    }


    /**
     * Write to system.log
     *
     * @return void
     */

    public function execute()
    {
        $enable = $this->scopeConfig->getValue('juno/general/enable', ScopeInterface::SCOPE_STORE);
        if (!$enable || !$this->validateServer()) {
            return;
        }

        $imageFolder = $this->scopeConfig->getValue('juno/general/image_folder', ScopeInterface::SCOPE_STORE);
        $imageFolder = explode(';', $imageFolder);
        foreach ($imageFolder as $folder) {
            $folder = $this->directoryList->getRoot() . '/' . $folder;
            $this->optimizeImage($folder);
        }
    }


    /**
     * validate the external server that handle the optimization
     *
     * @return bool
     */
    public function validateServer()
    {
        $server = $this->scopeConfig->getValue('juno/general/server', ScopeInterface::SCOPE_STORE);

        if (file_get_contents('http://' . $server) == 'ok') {
            return true;
        }
        return false;
    }

    /**
     * start optimize image found under $mediaPath
     *
     * @param $mediaPath string
     */
    function optimizeImage($mediaPath)
    {
        if (!file_exists($mediaPath)) {
            return;
        }
        $directory = new \RecursiveDirectoryIterator($mediaPath);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = new \RegexIterator($iterator, '/^.+\.(jpg)$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $filePath = array_shift($file);
            $imageUrl = $this->getImageUrl($filePath);
            $this->logger->info($imageUrl);
            $str = file_get_contents($this->getOptimizeImageUrl($imageUrl));
            if (strlen($str) > 1000) {
                file_put_contents($filePath, $str);
            }
        }
    }

    /**
     * get image url of the image
     *
     * @param $imagePath string
     * @return mixed
     */
    public function getImageUrl($imagePath)
    {
        $siteUrl = $this->_storeManager->getStore()->getBaseUrl();
        return str_replace($this->directoryList->getRoot() . '/', $siteUrl, $imagePath);
    }

    /**
     * @param $imageUrl string
     * @return string
     */
    public function getOptimizeImageUrl($imageUrl)
    {
        $server = $this->scopeConfig->getValue('juno/general/server', ScopeInterface::SCOPE_STORE);
        return 'http://' . $server . '/?img=' . $imageUrl;
    }
}
