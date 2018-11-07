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

class Codnitive_Frotel_Model_Setorder extends Mage_Core_Model_Abstract
{

	protected function _construct()
	{
		$this->_init('frotel/setorder');
	}

	public function loadByOrderId($orderId)
	{
		$this->_getResource()->loadByOrderId($this, $orderId);
		return $this;
	}

	public function saveResult($resultStr, $orderId)
	{
		$errors            = Mage::helper('frotel')->getErrorsList();
		$postTrakNumber    = null;
		$frotelOrderNumebr = null;

		$condition         = !array_key_exists($resultStr, $errors);
		if ($condition) {
			$result = explode('^^', $resultStr);
			$resultStr = $result[2];
			$postTrakNumber    = $result[1];
			$frotelOrderNumebr = $result[0];
		}

		$this->setOrderId($orderId)
			->setDescription($resultStr)
			->setPostTrackNumber($postTrakNumber)
			->setFrotelOrderNumber($frotelOrderNumebr)
			->save();
	}

}
