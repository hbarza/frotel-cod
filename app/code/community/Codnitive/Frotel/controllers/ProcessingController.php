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
 * @package    Codnitive_TejaratGateway
 * @author     Hassan Barza <support@codnitive.com>
 * @copyright  Copyright (c) 2012 CODNITIVE Co. (http://www.codnitive.com)
 * @license    http://www.codnitive.com/en/terms-of-service-softwares/ End User License Agreement (EULA 1.0)
 */

/**
 * Tejarat Bank Online Payment Controller
 *
 * @category   Codnitive
 * @package    Codnitive_TejaratGateway
 * @author     Hassan Barza <support@codnitive.com>
 */
class Codnitive_Frotel_ProcessingController extends Mage_Core_Controller_Front_Action
{

	protected $_failureBlockType     = 'frotel/transaction_failure';

	protected $_paymentInst;
	protected $_orderState;
	protected $_orderStatus;

	protected $_helper;

	public function _construct()
	{
		parent::_construct();
		$this->_helper = Mage::helper('frotel');
	}

	protected function _getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _getPendingPaymentStatus()
	{
		return $this->_getHelper()->getPendingPaymentStatus();
	}

	protected function _getHelper()
	{
		if (is_null($this->_helper)) {
			$this->_helper = Mage::helper('frotel');
		}
		return $this->_helper;
	}

	protected function _expireAjax()
	{
		if (!$this->_getCheckout()->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}

	public function redirectAction()
	{
		try {
			$session = $this->_getCheckout();
			$order = Mage::getModel('sales/order');

			$order->loadByIncrementId($session->getLastRealOrderId());

			if (!$order->getId()) {
				Mage::throwException('No order for processing found');
				return;
			}

			if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
				$order->setState(
						Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
						$this->_getPendingPaymentStatus(),
						$this->_getHelper()->__('Customer was redirected to Frotel gateway.')
				)->save();
			}

			if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
				$session->setFrotelGwQuoteId($session->getQuoteId());
				$session->setFrotelGwSuccessQuoteId($session->getLastSuccessQuoteId());
				$session->setFrotelGwRealOrderId($session->getLastRealOrderId());
				$session->getQuote()->setIsActive(false)->save();
				$session->clear();
			}

			$this->loadLayout();
			$this->renderLayout();
			return;
		}
		catch (Mage_Core_Exception $e) {
			$this->_catchMessages($e->getMessage());
		}
		catch (Exception $e) {
			$this->_catchMessages('An error occurred before redirection to Frotel gateway.',
					'Frotel Gateway redirection error: ' . $e->getMessage(),
					$e
			);
		}
		$this->_redirect('checkout/cart');
	}

	public function responseAction()
	{
		try {
			$response = $this->_checkResponse();

			if ($this->_order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
				$this->_getNewOrderStatus();
				$this->_order->setState(
						$this->_orderState, $this->_orderStatus, $this->_getHelper()->__('Customer back from Saman gateway.'), false
				);
			}

			if ($response['State'] === 'Canceled By User') {
				$this->_processCancel($response);
				return;
			}

			$api = Mage::getModel('frotel/api');
			$api->verifyOnlinePayment();
		}
		catch (Mage_Core_Exception $e) {
			$this->_catchMessages('Transaction response check: An error occurred in transaction.',
					'Saman response error: ' . $e->getMessage());
			$this->_failureBlock();
		}
		catch (Exception $e) {
			$this->_catchMessages('Transaction response: An unknown error occurred in transaction.',
					'Saman response error: ' . $e->getMessage());
			$this->_failureBlock();
		}
	}

	protected function _checkResponse()
	{
		if (!$this->getRequest()->isPost()) {
			Mage::throwException('Wrong request type.');
		}

		$request = $this->getRequest()->getParams();
		if (empty($request)) {
			Mage::throwException('Request doesn\'t contain any parameter.');
		}

		if (!isset($request['ResNum'])) {
			Mage::throwException('Transaction Reservation Number doesn\'t set.');
		}

		if (!isset($request['MID'])) {
			Mage::throwException('Merchant ID doesn\'t set.');
		}
		if ($request['MID'] !== Mage::getModel('samangateway/config')->getMerchantId()) {
			Mage::throwException('Merchant ID is not valid.');
		}

		if (!isset($request['State'])) {
			Mage::throwException('Order # ' . $request['ResNum'] . ': Transaction State doesn\'t set.');
		}

		$this->_order = Mage::getModel('sales/order')->loadByIncrementId($request['ResNum']);
		if (!$this->_order->getId()) {
			Mage::throwException('Order not found');
		}

		$this->_paymentInst = $this->_order->getPayment()->getMethodInstance();

		return $request;
	}

	protected function _catchMessages($sessionMessage = null, $debugMessage = null, $logE = null)
	{
		if (!is_null($sessionMessage)) {
			$this->_getCheckout()->addError($this->_getHelper()->__($sessionMessage));
		}

		if (!is_null($debugMessage)) {
			$this->_debug($debugMessage);
		}

		if (!is_null($logE)) {
			Mage::logException($logE);
		}
	}

	protected function _failureBlock()
	{
		$this->getResponse()->setBody(
				$this->getLayout()
				->createBlock($this->_failureBlockType)
				->toHtml()
		);
	}

	protected function _getNewOrderStatus()
	{
		$newOrderStatus = $this->_paymentInst->getConfigData('order_status');
		switch ($newOrderStatus) {
			case 'pending':
				$this->_orderState = Mage_Sales_Model_Order::STATE_NEW;
				$this->_orderStatus = 'pending';
				break;
			case 'processing':
				$this->_orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
				$this->_orderStatus = 'processing';
				break;
			case 'complete':
				$this->_orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
				$this->_orderStatus = 'complete';
				break;
			case 'closed':
				$this->_orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
				$this->_orderStatus = 'processing';
				break;
			case 'canceled':
				$this->_orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
				$this->_orderStatus = 'processing';
				break;
			case 'holded':
				$this->_orderState = Mage_Sales_Model_Order::STATE_HOLDED;
				$this->_orderStatus = 'holded';
				break;
			default:
				$this->_orderState = Mage_Sales_Model_Order::STATE_NEW;
				$this->_orderStatus = 'pending';
		}
	}

	protected function _debug($debugData)
	{
		if (Mage::getModel('frotel/config')->getDebugFlag()) {
			Mage::log($debugData, null, 'codnitive_' . Mage::getModel('frotel/method_online')->getCode() . '_payment_online.log', true);
		}
	}

}