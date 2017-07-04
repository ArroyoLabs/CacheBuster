<?php
/**
 *
 * @category    Arroyolabs
 * @package     Arroyolabs_CacheBuster
 * @copyright   Copyright (c) 2017 Arroyo Labs, Inc.
 * @author      john@arroyolabs.com
 */


class Arroyolabs_CacheBuster_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
     * @return string $seed
     */
    public function getDesignMergeSeed()
    {
        // @todo get current store?
        return Mage::getStoreConfig('arroyolabs/design_merge_seed/stores');
    }

    public function setDesignMergeSeed($seed = null)
    {
        $seed = ($seed == null) ? time() : $seed;
        $installer = new Mage_Core_Model_Resource_Setup;
        // $installer->startSetup();
        $installer->setConfigData('arroyolabs/design_merge_seed/stores', $seed);
        // $installer->endSetup();
        // error_log("seed should be: {$seed}");

        // @note We could refresh the config cache automatically.
        // Mage::app()->getCacheInstance()->cleanType('config');
        // @note Another option is to store this value differently
    }

    /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     * @todo extend this function to allow for improved naming -john
     */
    public function getMergedCssUrl($files)
    {
        // error_log("getMergedCssUrl: ".print_r($files, true));
        $designMergeSeed = $this->getDesignMergeSeed(); // This is neccessary to make it unique on every js/css cache flush
        // error_log("actual seed: {$designMergeSeed}");

        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}|{$designMergeSeed}") . '.css';
        $mergeFilesResult = $this->_mergeFiles(
            $files, $targetDir . DS . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );
        if ($mergeFilesResult) {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }

    /**
     * Merge specified javascript files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $designMergeSeed = $this->getDesignMergeSeed();

        $targetFilename = md5(implode(',', $files) . "|{$designMergeSeed}") . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }

    /**
     * Remove all merged js/css files
     *
     * @return  bool
     */
    public function cleanMergedJsCss()
    {
        // reset the cache buster seed
        $this->setDesignMergeSeed();

        return parent::cleanMergedJsCss();
    }

}
