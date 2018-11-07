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


class Codnitive_Frotel_Model_Carrier_Post
	extends Mage_Shipping_Model_Carrier_Abstract
	implements Mage_Shipping_Model_Carrier_Interface
{

	protected $_code = 'frotel';
	protected $_isFixed = true;

	protected function _getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _getConfig()
	{
		return Mage::getModel('frotel/config');
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!$this->_getConfig()->isActive() || !$this->getConfigFlag('active')) {
			return false;
		}

		if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
			foreach ($request->getAllItems() as $item) {
				if ($item->getParentItem()) {
					continue;
				}
				if ($item->getHasChildren() && $item->isShipSeparately()) {
					foreach ($item->getChildren() as $child) {
						if ($child->getProduct()->isVirtual()) {
							$request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
						}
					}
				}
				elseif ($item->getProduct()->isVirtual()) {
					$request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
				}
			}
		}

		$shippingTypes = $this->_getConfig()->getAvailableShippingTypes();
		$quote = $this->_getCheckout()->getQuote();
		$grandTotal    = $request->getPackageValue();
		$options['package_weight'] = $request->getPackageWeight();
		$buyStyles = $this->_getConfig()->getPeymentTypes();
		$options['destination_region'] = $quote->getShippingAddress()->getFrotelRegionId();
		$options['destination_city']   = $quote->getShippingAddress()->getFrotelCity();

		$api = Mage::getModel('frotel/api');
		$handlingFee = $api->getHandlingFee();
		$options['grand_total'] = $grandTotal + $handlingFee;

		$result = Mage::getModel('shipping/rate_result');

		foreach ($shippingTypes as $code) {
			$methodName = Mage::helper('frotel')->getShippingName($code);
			foreach ($buyStyles as $buyStyle => $styleTitle) {
				$options['buy_style'] = $buyStyle;
			$price = $api->getShippingPrice($options, $code);
			$price = urldecode($price);

			if (empty($price) || $price === 'Access Denied') {
				return false;
			}

			$method = Mage::getModel('shipping/rate_result_method');

			$method->setCarrier('frotel');
			$method->setCarrierTitle($this->getConfigData('title'));

			$method->setMethod($methodName . '_' . $buyStyle);
			$method->setMethodTitle(Mage::helper('frotel')->__(strtoupper($methodName)) . ' - ' . $styleTitle);

			$method->setPrice($price);
			$method->setCost(0.0);

			$result->append($method);
			}
		}

		return $result;
	}

	public function getAllowedMethods()
	{
		return array('frotel' => Mage::helper('frotel')->__('Frotel'));
	}

}
