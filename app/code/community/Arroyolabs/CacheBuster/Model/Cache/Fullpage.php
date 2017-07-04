<?php 
/**
 * Arroyolabs Cache_Fullpage model
 *
 * @category    Arroyolabs
 * @package     Arroyolabs_CacheBuster
 * @copyright   Copyright (c) 2017 Arroyo Labs, Inc.
 * @author      john@arroyolabs.com
 */

class Arroyolabs_CacheBuster_Model_Cache_Fullpage extends Mage_Core_Model_Abstract 
{
    protected $_cacheFolder = 'full_page_cache';

    /**
     * Delete the fullpage cache (via filesystem)
     */
    public function deleteFullpageCache()
    {
        $var = Mage::getBaseDir('var');
        $dirname = $var . "/" . $this->_cacheFolder;
    	$this->recursiveRemoveDirectory($dirname);
    }

    public function recursiveRemoveDirectory($directory)
    {
        foreach(glob("{$directory}/*") as $file)
        {
            if(is_dir($file)) { 
                $this->recursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($directory);
    }
}
