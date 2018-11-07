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

class Codnitive_Frotel_Model_Checkout_Observer extends Mage_Core_Model_Abstract
{

	protected $_config;
	protected $_customerId;

	protected function _getSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	public function __construct()
	{
		parent::__construct();
		$this->_config     = Mage::getModel('frotel/config');
	}

	public function sendOrderToFrotel(Varien_Event_Observer $observer)
	{
		if (!$this->_config->isActive()) {
			return;
		}

		$order       = Mage::getModel('sales/order')->load($this->_getSession()->getLastOrderId());
		$paymentType = $order->getPayment()->getMethodInstance()->getCode();
		$paymentTypes = Mage::helper('frotel')->getPaymentTypes();
		unset($paymentTypes['frotelonline']);

		if (!array_key_exists($paymentType, $paymentTypes)) {
			return;
		}

		$customer = Mage::getModel('customer/customer')->load(
			$order->getShippingAddress()->getCustomerId());

		$data     = array(
			'customer' => $customer,
			'order'    => $order
		);

		$result    = Mage::getModel('frotel/api')->sendOrderToFrotel($data);
		Mage::getModel('frotel/setorder')->saveResult($result, $order->getId());
	}

}