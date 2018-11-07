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

class Codnitive_Frotel_Model_Resource_Setorder extends Codnitive_Frotel_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('frotel/setorder', 'value_id');
	}

	public function loadByOrderId(Codnitive_Frotel_Model_Setorder $information, $orderId)
	{
		$adapter = $this->_getReadAdapter();
		$bind    = array('order_id' => $orderId);
		$select  = $adapter->select()
			->from($this->getTable('frotel/setorder'))
			->where('order_id = ?', $orderId);

		$informationId = $adapter->fetchOne($select, $bind);
		if ($informationId) {
			$this->load($information, $informationId);
		} else {
			$information->setData(array());
		}

		return $this;
	}
}
