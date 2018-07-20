<?php
/**
* @license Copyright 2016-2018 CryptoMarket Inc., MIT License
* @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
*/

class Cryptomarket_Core_Model_Observer {

	public function implementOrderStatus($e) {
		$order = $e -> getOrder();
		$paymentCode = $order -> getPayment() -> getMethodInstance() -> getCode();
		if ($paymentCode == 'cryptomarket') {
			$order -> setState(Mage_Sales_Model_Order::STATE_NEW, true);
			$order -> save();
		}

		//	Mage::log('$order = $event->getOrder();' . $order -> getState());
	}

	/*
	* Queries CryptoMarket to update the order states in magento to make sure that
	* open orders are closed/canceled if the CryptoMarket invoice expires or becomes
	* invalid.
	*/
	public function updateOrderStates() {
		$apiKey = \Mage::getStoreConfig('payment/cryptomarket/api_key');

		if (false === isset($apiKey) || empty($apiKey)) {
			\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::updateOrderStates() could not start job to update the order states because the API key was not set.');
			return;
		} else {
			\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::updateOrderStates() started job to query CryptoMarket to update the existing order states.');
		}

		/*
		* Get all of the orders that are open and have not received an IPN for
		* complete, expired, or invalid.
		*/
		$orders = \Mage::getModel('cryptomarket/payorder') -> getOpenOrders();

		if (false === isset($orders) || empty($orders)) {
			\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::updateOrderStates() could not retrieve the open orders.');
			return;
		} else {
			\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::updateOrderStates() successfully retrieved existing open orders.');
		}

		/*
		* Get all orders that have been paid using cryptomarket and
		* are not complete/closed/etc
		*/
		foreach ($orders as $order) {
			/*
			* Query CryptoMarket with the invoice ID to get the status. We must take
			* care not to anger the API limiting gods and disable our access
			* to the API.
			*/
			$status = null;

			// TODO:
			// Does the order need to be updated?
			// Yes? Update Order Status
			// No? continue
		}

		\Mage::helper('cryptomarket') -> debugData('[INFO] Cryptomarket_Core_Model_Observer::updateOrderStates() order status update job finished.');
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
	 * [createPayOrder description]
	 * @param  [type] $event [description]
	 * @return [type]        [description]
	 */
	public function createPayOrder($event)
	{
		// $order = $event->getOrder();
		// error_log('[INFO] ORDER'.$order->getAllVisibleItems());
		$order = $event->getOrder();
		// $payment = $order->getPayment()->getMethodInstance();

		$payload = array(
			'payment_receiver' => \Mage::getStoreConfig('payment/cryptomarket/payment_receiver'),
			'to_receive_currency' => \Mage::app()->getStore()->getCurrentCurrencyCode(),
			'to_receive' => $order->getGrandTotal(),
			'external_id' => $order->getId(),
			'callback_url' => \Mage::helper('cryptomarket')->getNotificationUrl(),
			'error_url' => \Mage::helper('cryptomarket')->getFailureUrl(),
			'success_url' => \Mage::helper('cryptomarket')->getRedirectUrl(),
			'refund_email' => $order->getCustomerEmail(),
			'language' => explode('_', \Mage::app()->getLocale()->getLocaleCode())[0]
		);

		$client = \Mage::helper('cryptomarket')->getCryptomarketClient();

		$result = $client->createPayOrder($payload);

		error_log('[INFO] payload result: '.json_encode($result));

        // foreach ($order->getAllVisibleItems() as $item){
        //    $item->getQtyOrdered(); // Number of item ordered
        //    //$item->getQtyShipped()
        //    //$item->getQtyInvoiced()
		//
        //    $optionArray = $item->getProductOptions();
        //    // Todo : check to see if set and is array $optionArray['options']
        //    foreach($optionArray['options'] as $option){
        //        // Mage::log($option)
        //        //echo $option['label']
        //        //$option['value']
        //    }
        // }
		// //get order
		// $order = \Mage::getModel('sales/order')->load($lastOrderId);
		// if (false === isset($order) || true === empty($order)) {
		// 	\Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Model_Observer::createPayOrder(), Invalid Order ID received.');
		// 	return;
		// }
		// //check if order is pending
		// if($order->getStatus() != 'pending')
		// {
		// 	return;
		// }
		//
		// \Mage::helper('cryptomarket')->registerAutoloader();
		//
		// \Mage::helper('cryptomarket')->registerAutoloader();
		//
		// $cryptomkt_apikey = \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apikey');
		// $cryptomkt_apisecret = \Mage::getStoreConfig('payment/cryptomarket/cryptomkt_apisecret');
		//
		// $configuration = Cryptomkt\Exchange\Configuration::apiKey($cryptomkt_apikey, $cryptomkt_apisecret);
		// $client = Cryptomkt\Exchange\Client::create($configuration);

	}

	/**
    * Event Hook: checkout_onepage_controller_success_action
    * @param $observer Varien_Event_Observer
    */
    public function redirectToCartIfExpired(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()->getName() == 'checkout_onepage_controller_success_action')
        {
            $lastOrderId = null;
            foreach(\Mage::app()->getRequest()->getParams() as $key=>$value)
            {
                if($key == 'order_id')
                    $lastOrderId = $value;
            }
           if($lastOrderId != null)
           {
                //get order
                $order = \Mage::getModel('sales/order')->load($lastOrderId);
                if (false === isset($order) || true === empty($order)) {
                    \Mage::helper('bitpay')->debugData('[ERROR] In Bitpay_Core_Model_Observer::redirectToCartIfExpired(), Invalid Order ID received.');
                    return;
                }
                //check if order is pending
                if($order->getStatus() != 'pending')
                {
                    return;
                }

                //check if invoice for order exist in bitpay_invoices table
                $bitpayInvoice = \Mage::getModel('bitpay/invoice')->load($order->getIncrementId(), 'increment_id');
                $bitpayInvoiceData = $bitpayInvoice->getData();
                //if is empty or not is array abort
                if(!is_array($bitpayInvoiceData) || is_array($bitpayInvoiceData) && empty($bitpayInvoiceData))
                    return;
                //check if bitpay invoice id expired
                $invoiceExpirationTime = $bitpayInvoiceData['expiration_time'];
                if($invoiceExpirationTime < strtotime('now'))
                {
                    $failure_url = \Mage::getUrl(\Mage::getStoreConfig('payment/bitpay/failure_url'));
                    \Mage::app()->getResponse()->setRedirect($failure_url)->sendResponse();
                }
            }
        }
    }
}
