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
class Codnitive_Frotel_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
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

	public function getCountryHtmlSelect($type)
	{
		$countryId = $this->getCountryId();
		$select = $this->getLayout()->createBlock('core/html_select')
			->setName($type.'[country_id]')
			->setId($type.':country_id')
			->setTitle(Mage::helper('checkout')->__('Country'))
			->setClass('validate-select')
			->setValue($countryId)
			->setOptions($this->getCountryOptions())
			->setExtraParams('onchange="Codnitive.frotel.flipFields(this, \'billing\')"');
		if ($type === 'shipping') {
			$select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
		}

		return $select->getHtml();
	}

	public function getFrotelRegions()
	{
		return $this->helper('frotel')->getFrotelRegions();
	}
}
