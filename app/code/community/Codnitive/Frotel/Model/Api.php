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

class Codnitive_Frotel_Model_Api
{
	const SOAP_CLIENT_URL = 'http://www.froservice.ir/F-W-S-L/F_Gateway.php?wsdl';

	protected function _getConfig()
	{
		return Mage::getModel('frotel/config');
	}

	protected function _getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _connect()
	{
		try {
			$client = new SoapClient(self::SOAP_CLIENT_URL);

			if (!$client) {
				Mage::log($client, null, 'codnitive_frotel.log', true);
				Mage::throwException($client);
				return false;
			}
			return $client;
		}
		catch (Mage_Core_Exception $e) {
			Mage::log($e->getMessage(), null, 'codnitive_frotel.log', true);
			return false;
		}
		catch (Exception $e){
			Mage::log($e->getMessage(), null, 'codnitive_frotel.log', true);
			Mage::logException($e);
			return false;
		}
	}

	protected function _getUsername()
	{
		return $this->_getConfig()->getUsername();
	}

	protected function _getPassword()
	{
		return $this->_getConfig()->getPassword();
	}

	protected function _getOrigRegion()
	{
		return $this->_getConfig()->getOrigRegion();
	}

	protected function _getOrigCity()
	{
		return $this->_getConfig()->getOrigCity();
	}

	public function getHandlingFee()
	{
		$client = $this->_connect();
		return $client->FKhadamat();
	}

	public function getShippingPrice($options, $sendType)
	{
		$total      = (string) $options['grand_total'];
		$weight     = (string) $options['package_weight'];
		$payment    = (string) $options['buy_style'];
		$origRegion = (string) $this->_getOrigRegion();
		$origCity   = (string) $this->_getOrigCity();
		$desRegion  = (string) $options['destination_region'];
		$desCity    = (string) $options['destination_city'];
		$username   = (string) $this->_getUsername();
		$password   = (string) $this->_getPassword();

		$client = $this->_connect();
		$price  = $client->FCalcPPrice(
			$total, $weight, $payment, $sendType, $origRegion, $origCity,
			$desRegion, $desCity, $username, $password
		);

		return $price;
	}

	public function sendOrderToFrotel($data)
	{
		$helper     = Mage::helper('frotel');
		$customer   = $data['customer'];
		$order      = $data['order'];
		$address    = $order->getShippingAddress();

		$firstName  = (string) $customer->getFirstname();
		$lastName   = (string) $customer->getLastname();
		$gender     = (string) '';
		$email      = (string) $customer->getEmail();
		$region     = (string) $address->getFrotelRegionId();
		$city       = (string) $address->getFrotelCity();
		$street     = $address->getStreet();
		$street     = (string) $street[0];
		$postcode   = (string) $address->getPostcode();
		$telephone  = (string) $address->getTelephone();
		$cellphone  = (string) '';
		$message    = (string) '';
		$shippingId = (string) $helper->getShippingCode($order->getShippingMethod());
		$products   = (string) $this->_getProductsList($order);
		$paymentId  = (string) $helper->getPeymentCode($order->getPayment()->getMethodInstance()->getCode());
		$draft      = (string) '';
		$bank       = (string) '';
		$marketer   = (string) 'fs';
		$url        = (string) Mage::getModel('frotel/method_online')->getResponseUrl();
		$username   = (string) $this->_getUsername();
		$password   = (string) $this->_getPassword();

		$client = $this->_connect();
		$result = $client->FSetOrder(
			$firstName, $lastName, $gender, $email, $region, $city, $street,
			$postcode, $telephone, $cellphone, $message, $shippingId, $products, $paymentId,
			$draft, $bank, $marketer, $url, $username, $password);

		$result = urldecode($result);
		return $result;
	}

	public function verifyOnlinePayment($orderId, $response)
	{
		$helper     = Mage::helper('frotel');
		$data       = Mage::getModel('frotel/setorder')->loadByOrderId($orderId);

		Mage::log($data);

		$resNum     = (string) $data->getFrotelOrderNumber();
		$refNum     = (string) $response['RefNum'];
		$state      = (string) $response['State'];
		$url        = (string) Mage::getModel('frotel/method_online')->getResponseUrl();
		$username   = (string) $this->_getUsername();
		$password   = (string) $this->_getPassword();

		$client = $this->_connect();
		$result = $client->FVerifyEndbuy($resNum, $refNum, $state, $url, $username, $password);

		$result = urldecode($result);

		Mage::log($result);

		return $result;
	}

	protected function _getProductsList($order)
	{
		$listStr    = '';
		$orderItems = $order->getAllItems();
		foreach($orderItems as $item) {
			$sku        = $item->getSku();
			$name       = $item->getName();
			$price      = $item->getPrice();
			$weight     = $item->getWeight();
			$qty        = $item->getQtyOrdered();
			$commission = 0;
			$discount   = floatval($item->getDiscountPercent());

			$listStr .= "$sku^$name^$price^$weight^$qty^$commission^$discount;";
		}

		$listStr = rtrim($listStr, ';');
		return $listStr;
	}
}
