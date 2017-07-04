<?php
/**
 * CacheBuster Observer
 *
 * @category    Arroyolabs
 * @package     Arroyolabs_CacheBuster
 * @copyright   Copyright (c) 2017 Arroyo Labs, Inc.
 * @author      john@arroyolabs.com
 * @note 		borrowed some code from http://www.joomlacreator.com/downloads/magento-extensions/magento-cache-shell-tool
 */


class Arroyolabs_CacheBuster_Model_Observer
{
    public function deleteFullpageCache()
    {
    	$model = Mage::getModel('arroyolabs_cachebuster/cache_fullpage');
    	$model->deleteFullpageCache();

    	return "Fullpage cache has been flushed\n";
    }

    /**
     * Flush all cache (except images)
     * @return string $message
     */
    public function flushAll()
    {
    	$message = "";

    	$types = $this->_parseCacheTypeString('all');
    	$this->refresh($types);

    	$message .= $this->cleanMergedFiles();
    	$message .= $this->flushCacheStorage();
    	$message .= $this->flushSystemCache();

    	$message .= $this->deleteFullpageCache();

    	return $message;
    }

    /**
     * flush "fast" cache
     * @return string $message
     */
    public function flushCacheStorage()
    {
    	$message = "";

    	try {
			Mage::app()->getCacheInstance()->flush();
			$message = "The cache storage has been flushed.\n";
		} catch (Exception $e) {
			$message = "Exception:\n" . $e . "\n";
		}

		return $message;
    }

    /**
     * Flush "slow" cache
     * @return string $message
     */
    public function flushSystemCache()
    {
    	$message = "";

    	try {
			Mage::app()->cleanCache();
			$message = "The system cache has been flushed.\n";
		} catch (Exception $e) {
			$message =  "Exception:\n" . $e . "\n";
		}

		return $message;
    }

    /**
    * Flush merged js & css files
    * @return string $message
    */
    public function cleanMergedFiles()
    {
    	$message = "";

    	try {
			Mage::getModel('core/design_package')->cleanMergedJsCss();
			Mage::dispatchEvent('clean_media_cache_after');
			$message = "The JavaScript/CSS cache has been cleaned.\n";
		} catch (Exception $e) {
			$message = "An error occurred while clearing the JavaScript/CSS cache.\n" . $e->getMessage() . "\n";
		}

		return $message;
    }

	/**
	 * Delete the image cache
	 * @return string $message
	 */
	public function cleanImages() {
		$message = "";

		try {
			Mage::getModel('catalog/product_image')->clearCache();
			Mage::dispatchEvent('clean_catalog_images_cache_after');
			$message = "The image cache was cleaned.\n";
		} catch (Mage_Core_Exception $e) {
			$message = $this->_getSession()->addError($e->getMessage());
		}

		return $message;
	}

	/**
	 * Returns a list of cachetypes, and their current cache status.
	 *
	 * @param string $string
	 * @return array
	 */
	protected function _parseCacheTypeString($string) {
		$cachetypes = array();
		if ($string == 'all') {
			$collection = $this->_getCacheTypeCodes();
			foreach ($collection as $cache) {
				$cachetypes[] = $cache;
			}
		} else if (!empty($string)) {
			$codes = explode(',', $string);
			foreach ($codes as $code) {
				$cachetypes[] = $code;
			}
		}

		return $cachetypes;
	}

	/**
	 * Gets Magento cache types.
	 * @return
	 */
	private function _getCacheTypes() {
		return Mage::getModel('core/cache')->getTypes();
	}

	/**
	 * Gets an array of cache type code.
	 * @return array Cache type codes.
	 */
	private function _getCacheTypeCodes() {
		return array_keys($this->_getCacheTypes());
	}

	/**
	 * Refreshes caches for the provided cache types.
	 * @param  $types
	 * @return void
	 */
	public function refresh($types) {
		$updatedTypes = 0;
		$message = "";

		if (!empty($types)) {
			foreach ($types as $type) {
				try {
					$tags = Mage::app()->getCacheInstance()->cleanType($type);
					$updatedTypes++;
				} catch (Exception $e) {
					$message .= "{$type} cache unknown error:\n" . $e->getMessage() . "\n";
				}
			}
		}
		if ($updatedTypes > 0) {
			$message .= "{$updatedTypes} cache type(s) refreshed.\n";
		}
	}
}
