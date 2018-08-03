<?php
/**
* @license Copyright 2016-2018 CryptoMarket Inc., MIT License
* @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
*/

class Cryptomarket_Core_Model_Observer {
	protected $_dataSaveAllowed = false;

	public function implementOrderStatus($e) {
		$order = $e -> getOrder();
		$paymentCode = $order -> getPayment() -> getMethodInstance() -> getCode();
		if ($paymentCode == 'bitpay') {
			$order -> setState(Mage_Sales_Model_Order::STATE_NEW, true);
			$order -> save();
		}
		//	Mage::log('$order = $event->getOrder();' . $order -> getState());
	}

	/**
	* Method that is called via the magento cron to update orders if the
	* invoice has expired
	*/
	public function cleanExpired() {
		\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::cleanExpired() called.');
		\Mage::helper('cryptomarket') -> cleanExpired();
	}

	/**
	 * createPayOrder create a new cryptocompra pay order
	 * @param  Object $event
	 * @return Null
	 */
	public function createPayOrder($event)
	{
		$order = $event->getOrder();

		//check if order is pending
		if($order->getStatus() != 'pending')
		{
			return;
		}

		$payload = array(
			'payment_receiver' => \Mage::getStoreConfig('payment/cryptomarket/payment_receiver'),
			'to_receive_currency' => \Mage::app()->getStore()->getCurrentCurrencyCode(),
			'to_receive' => $order->getGrandTotal(),
			'external_id' => $order->getIncrementId(),
			'callback_url' => \Mage::helper('cryptomarket')->getNotificationUrl(),
			'error_url' => \Mage::helper('cryptomarket')->getFailureUrl(),
			'success_url' => \Mage::helper('cryptomarket')->getRedirectUrl(),
			'refund_email' => $order->getCustomerEmail(),
			'language' => explode('_', \Mage::app()->getLocale()->getLocaleCode())[0]
		);

		$client = \Mage::helper('cryptomarket')->getCryptomarketClient();

		$result = $client->createPayOrder($payload);

		if( $result->status === "success" ){ error_log('[INFO] success pay order');
			Mage::getModel('cryptomarket/method_redirect')->setRedirectToCryptoMarket($result->data->payment_url);
			return;
		}
		else{ error_log('[INFO] error pay order');
			Mage::getModel('cryptomarket/method_redirect')->setRedirectToCryptoMarket(\Mage::helper('cryptomarket')->getFailureUrl());

			$order->cancel();
			$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancel Transaction. By '.$result->message);
			$order->setStatus("canceled");
			$order->save();
			Mage::getSingleton('core/session')->addError('Order was canceled by '.$result->message.'. Please try again or contact with the website administrator.');
			Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Model_Observer::createPayOrder(), Error creating pay order: '.json_encode($result));
		}
	}
}
