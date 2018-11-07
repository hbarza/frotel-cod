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

class Codnitive_Frotel_Helper_Data extends Mage_Core_Helper_Data
{

	private $_url = 'http://extension.codnitive.com/status/';

	protected $_frotelRegions = array(
		'41' => 'East Azarbaijan',          '44' => 'West Azarbaijan',
		'45' => 'Ardebil',                  '31' => 'Isfahan',
		'84' => 'Ilam',                     '77' => 'Booshehr',
		'26' => 'Alborz',                   '21' => 'Tehran',
		'38' => 'Charmahal-o Bakhtiyari',   '56' => 'South Khorasan',
		'51' => 'Razavi Khorasan',          '58' => 'North Khorasan',
		'61' => 'Khoozestan',               '24' => 'Zanjan',
		'23' => 'Semnan',                   '54' => 'Sistan-o Baloochestan',
		'71' => 'Fars',                     '28' => 'Ghazvin',
		'25' => 'Ghom',                     '87' => 'Kordestan',
		'34' => 'Kerman',                   '83' => 'Kermanshah',
		'74' => 'Kohkiloye-o Boyerahmad',   '17' => 'Golestan',
		'13' => 'Gilan',                    '66' => 'Lorestan',
		'15' => 'Mazandran',                '86' => 'Markazi',
		'76' => 'Hormozgan',                '81' => 'Hamedan',
		'35' => 'Yazd'
	);

	protected $_frotelShippingTypes = array(
		'1' => 'ems',
		'2' => 'rms',
		'3' => 'lms'
	);

	protected $_frotelPaymentTypes = array(
		'frotel'           => 'posti',
		'frotelonline'     => 'saman',
		'frotelmoneyorder' => 'havale'
	);

	protected $_errorMsgs = array(
		'Access Denied'            => 'You can not access to account.',
		'Data Error'               => 'Sent data is not complete.',
		'Province Error'           => 'Region ID is wrong.',
		'City Error'               => 'City ID is wrong.',
		'Product Register Error'   => 'An error occurred when saving prodcts list.',
		'Order Register Error'     => 'An error occurred when saving order or recipient data.',
		'Create Faktor Error'      => 'An error occurred when creating invoice.',
		'Empty Product List Error' => 'Products list is empty or it has wrong format'
	);

	public function __construct()
	{
		if (time() > (int)$this->getLastCheck() + (int)$this->getFrq()) {
			$this->setLastCheck();
		}
	}

	public function getFrotelRegions()
	{
		return $this->_frotelRegions;
	}

	public function getShippingTypes()
	{
		return $this->_frotelShippingTypes;
	}

	public function getShippingName($code)
	{
		$shippingTypes = $this->getShippingTypes();
		return $shippingTypes[$code];
	}

	public function getShippingCode($shippingMethod)
	{
		$name = str_replace('frotel_', '', $shippingMethod);
		return array_search($name, $this->getShippingTypes());
	}

	public function getPaymentTypes()
	{
		return $this->_frotelPaymentTypes;
	}

	public function getPeymentCode($paymentMethod)
	{
		$paymentTypes = $this->getPaymentTypes();
		return $paymentTypes[$paymentMethod];
	}

	public function getErrorsList()
	{
		return $this->_errorMsgs;
	}

	public function getErrorMessageText($errorCode)
	{
		$errors = $this->getErrorsList();
		return $errors[$errorCode];
	}

	public function isIrCountryId($countryCode)
	{
		return $countryCode === 'IR';
	}

	public function getFrq()
	{
		return Mage::getStoreConfig(Codnitive_Frotel_Model_Config::getNamespace() . 'chkfrq');
	}

	public function getLastCheck()
	{
		$namespace = Codnitive_Frotel_Model_Config::EXTENSION_NAMESPACE;
		return Mage::app()->loadCache('codnitive_'.$namespace.'_lastcheck');
	}

	public function setLastCheck()
	{
		$namespace = Codnitive_Frotel_Model_Config::EXTENSION_NAMESPACE;
		Mage::app()->saveCache(time(), 'codnitive_'.$namespace.'_lastcheck');
		return $this;
	}

	public function getConUrl()
	{
		return $this->_url;
	}

	public function curl($inf = null, $url = null)
	{
		$url = ($url === null) ? $this->_url : $url;

		try {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $inf);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		}
		catch (Exception $e) {
			return false;
		}
	}

	protected function _checkCert()
	{
		$nameSpace = Codnitive_Frotel_Model_Config::getNamespace();

		$sernum = Mage::getStoreConfig($nameSpace . 'sernum');
		$regcod = Mage::getStoreConfig($nameSpace . 'regcod');
		$ownnam = Mage::getStoreConfig($nameSpace . 'ownnam');
		$ownmai = Mage::getStoreConfig($nameSpace . 'ownmai');

		try {
			$condition = empty($sernum) || !$sernum || empty($regcod) || !$regcod
				|| empty($ownnam) || !$ownnam || empty($ownmai) || !$ownmai;

			$crypt = Varien_Crypt::factory()->init('3ee2a23ba72ce85081fae961d2e51b1b');
			$inf = array(
				'sn' => base64_encode($crypt->encrypt($crypt->decrypt((string)$sernum))),
				'rc' => base64_encode($crypt->encrypt($crypt->decrypt((string)$regcod))),
				'on' => base64_encode($crypt->encrypt((string)$ownnam)),
				'om' => base64_encode($crypt->encrypt((string)$ownmai)),
				'bu' => base64_encode($crypt->encrypt((string)Mage::getStoreConfig('web/unsecure/base_url'))),
				'en' => base64_encode($crypt->encrypt((string)Codnitive_Frotel_Model_Config::EXTENSION_NAME)),
				'ev' => base64_encode($crypt->encrypt((string)Codnitive_Frotel_Model_Config::EXTENSION_VERSION)),
				'es' => base64_encode($crypt->encrypt((string)Mage::getStoreConfig($nameSpace . 'active'))),
			);

			$data = $this->curl($inf);

			if ($condition || '0' === $data) {
				Mage::getConfig()->saveConfig($nameSpace.'active', 0)->reinit();
				Mage::app()->reinitStores();
			}

		}
		catch (Exception $e) {
			Mage::getConfig()->saveConfig($nameSpace.'active', 0)->reinit();
			Mage::app()->reinitStores();
		}
	}

	public function getPendingPaymentStatus()
	{
		if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
			return Mage_Sales_Model_Order::STATE_HOLDED;
		}
		return Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
	}

}
