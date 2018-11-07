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
 * Cash on delivery payment method model
 */
class Codnitive_Frotel_Model_Method_Moneyorder extends Mage_Payment_Model_Method_Abstract
{

	protected $_code  = 'frotelmoneyorder';
	protected $_infoBlockType = 'frotel/payment_info';

	public function isAvailable($quote = null)
	{
		$shippingMethod = Mage::getSingleton('checkout/session')->getQuote()
			->getShippingAddress()->getShippingMethod();

		$condition = !Mage::getModel('frotel/config')->isActiveMoneyOrderPayment()
			|| (int)substr($shippingMethod, -1, 1) !== 0;
		if ($condition) {
			return false;
		}

		return parent::isAvailable($quote);
	}

}
