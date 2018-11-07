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

class Codnitive_Frotel_Block_Payment_Checkout_Redirect extends Mage_Core_Block_Template
{

	protected function _getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _getOrder()
	{
		if ($this->getOrder()) {
			return $this->getOrder();
		}
		elseif ($orderIncrementId = $this->_getCheckout()->getLastRealOrderId()) {
			return Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		}
		else {
			return null;
		}
	}

	public function getPageContent()
	{
		$order    = $this->_getOrder();
		$customer = Mage::getModel('customer/customer')->load(
			$order->getShippingAddress()->getCustomerId());

		$data     = array(
			'customer' => $customer,
			'order'    => $order
		);

		$result = Mage::getModel('frotel/api')->sendOrderToFrotel($data);
		Mage::getModel('frotel/setorder')->saveResult($result, $order->getId());

		$result = explode('^^', $result);
		return $result[2];
	}

}