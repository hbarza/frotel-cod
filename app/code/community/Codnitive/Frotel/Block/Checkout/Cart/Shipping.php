<?php
/**
 * CODNITIVE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE_EULA.html.
 * It is also available through the world-wide-web at this URL:
 * http://www.codnitive.com/en/terms-of-service-softwares/
 * http://www.codnitive.com/fa/terms-of-service-softwares/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   Codnitive
 * @package    Codnitive_Frotel
 * @author     Hassan Barza <support@codnitive.com>
 * @copyright  Copyright (c) 2012 CODNITIVE Co. (http://www.codnitive.com)
 * @license    http://www.codnitive.com/en/terms-of-service-softwares/ End User License Agreement (EULA 1.0)
 */

/**
 * One page checkout status
 *
 * @category   Codnitive
 * @package    Codnitive_Frotel
 * @author     Hassan Barza <support@codnitive.com>
 */
class Codnitive_Frotel_Block_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
	public function getCountryId()
	{
		$countryId = $this->getAddress()->getCountryId();
		if (is_null($countryId)) {
			$countryId = $this->helper('core')->getDefaultCountry();
		}
		return $countryId;
	}

	public function isIrCountryId()
	{
		return $this->helper('frotel')->isIrCountryId($this->getCountryId());
	}

	public function getCountryHtmlSelect($defValue=null, $name='country_id', $id='country', $title='Country')
	{
		Varien_Profiler::start('TEST: '.__METHOD__);
		if (is_null($defValue)) {
			$defValue = Mage::getBlockSingleton('directory/data')->getCountryId();
		}
		$cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_'.Mage::app()->getStore()->getCode();
		if (Mage::app()->useCache('config') && $cache = Mage::app()->loadCache($cacheKey)) {
			$options = unserialize($cache);
		} else {
			$options = Mage::getBlockSingleton('directory/data')->getCountryCollection()->toOptionArray();
			if (Mage::app()->useCache('config')) {
				Mage::app()->saveCache(serialize($options), $cacheKey, array('config'));
			}
		}
		$html = $this->getLayout()->createBlock('core/html_select')
			->setName($name)
			->setId($id)
			->setTitle(Mage::helper('directory')->__($title))
			->setClass('validate-select')
			->setValue($defValue)
			->setOptions($options)
			->setExtraParams('onchange="Codnitive.frotel.flipFields(this, \'estimate\')"')
			->getHtml();

		Varien_Profiler::stop('TEST: '.__METHOD__);
		return $html;
	}

	public function getFrotelRegions()
	{
		return $this->helper('frotel')->getFrotelRegions();
	}
}
