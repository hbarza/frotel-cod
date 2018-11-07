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
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Codnitive_Frotel_Override_Checkout_CartController extends Mage_Checkout_CartController
{
	protected function _getConfig()
	{
		return Mage::getModel('frotel/config');
	}

	public function estimatePostAction()
	{
		if (!$this->_getConfig()->isActive()) {
			return parent::estimatePostAction();
		}

		$country        = (string) $this->getRequest()->getParam('country_id');
		$postcode       = (string) $this->getRequest()->getParam('estimate_postcode');
		$city           = (string) $this->getRequest()->getParam('estimate_city');
		$regionId       = (string) $this->getRequest()->getParam('region_id');
		$region         = (string) $this->getRequest()->getParam('region');
		$frotelRegionId = (string) $this->getRequest()->getParam('frotel_region_id');
		$frotelCity     = (string) $this->getRequest()->getParam('frotel_city');

		$this->_getQuote()->getShippingAddress()
			->setCountryId($country)
			->setCity($city)
			->setPostcode($postcode)
			->setRegionId($regionId)
			->setRegion($region)
			->setFrotelRegionId($frotelRegionId)
			->setFrotelCity($frotelCity)
			->setCollectShippingRates(true);
		$this->_getQuote()->save();
		$this->_goBack();
	}
}
