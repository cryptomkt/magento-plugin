<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 * @route cryptomarket/updater
 */
class Cryptomarket_Core_UpdaterController extends Mage_Core_Controller_Front_Action
{
    /**
     * cryptomarket's updater lands here
     *
     * @route cryptomarket/updater
     * @route cryptomarket/updater/index
     */
    public function indexAction()
    {
        if (false === ini_get('allow_url_fopen')) {
            ini_set('allow_url_fopen', true);
        }

        $raw_post_data = file_get_contents('php://input');

        if (false === $raw_post_data) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Could not read from the php://input stream or invalid Cryptomarket IPN received.');
            throw new \Exception('Could not read from the php://input stream or invalid Cryptomarket IPN received.');
        }

        \Mage::helper('cryptomarket')->registerAutoloader();

        \Mage::helper('cryptomarket')->debugData(array(sprintf('[INFO] In Cryptomarket_Core_IpnController::indexAction(), Incoming IPN message from CryptoMarket: '),$raw_post_data,));

        // Magento doesn't seem to have a way to get the Request body
        $payload = json_decode($raw_post_data);

        if(isset($payload->data))
        {
            $payload = $payload->data;
        }

        if (true === empty($payload)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Could not decode the JSON payload from CryptoMarket.');
            throw new \Exception('Could not decode the JSON payload from CryptoMarket.');
        }

        if (true === empty($payload->id) || false === isset($payload->posData)) {
            \Mage::helper('cryptomarket')->debugData(sprintf('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Did not receive order ID in IPN: ', $payload));
            throw new \Exception('Invalid Cryptomarket payment notification message received - did not receive order ID.');
        }

        $payload->posData     = is_string($payload->posData) ? json_decode($payload->posData) : $payload->posData;
        $payload->buyerFields = isset($payload->buyerFields) ? $payload->buyerFields : new stdClass();

        \Mage::helper('cryptomarket')->debugData($payload);

        // Log IPN
        $magePayload = \Mage::getModel('cryptomarket/payorder')->addData(
            array(
                'invoice_id'       => isset($payload->id) ? $payload->id : '',
                'url'              => isset($payload->url) ? $payload->url : '',
                'pos_data'         => json_encode($payload->posData),
                'status'           => isset($payload->status) ? $payload->status : '',
                'price'            => isset($payload->price) ? $payload->price : '',
                'currency'         => isset($payload->currency) ? $payload->currency : '',
                'invoice_time'     => isset($payload->invoiceTime) ? intval($payload->invoiceTime / 1000) : '',
                'expiration_time'  => isset($payload->expirationTime) ? intval($payload->expirationTime / 1000) : '',
                'current_time'     => isset($payload->currentTime) ? intval($payload->currentTime / 1000) : '',
                'exception_status' => isset($payload->exceptionStatus) ? $payload->exceptionStatus : '',
                'transactionCurrency' => isset($payload->transactionCurrency) ? $payload->transactionCurrency : ''
            )
        )->save();


        // Order isn't being created for iframe...
        if (isset($payload->posData->orderId)) {
            $order = \Mage::getModel('sales/order')->loadByIncrementId($payload->posData->orderId);
        } else {
            $order = \Mage::getModel('sales/order')->load($payload->posData->quoteId, 'quote_id');
        }

        if (false === isset($order) || true === empty($order)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Invalid Cryptomarket IPN received.');
            \Mage::throwException('Invalid Cryptomarket IPN received.');
        }

        $orderId = $order->getId();
        if (false === isset($orderId) || true === empty($orderId)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Invalid Cryptomarket IPN received.');
            \Mage::throwException('Invalid Cryptomarket IPN received.');
        }

        /**
         * Ask CryptoMarket to retreive the invoice so we can make sure the invoices
         * match up and no one is using an automated tool to post IPN's to merchants
         * store.
         */
        $invoice = \Mage::getModel('cryptomarket/method_redirect')->fetchInvoice($payload->id);

        if (false === isset($invoice) || true === empty($invoice)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Could not retrieve the invoice details for the ipn ID of ' . $payload->id);
            \Mage::throwException('Could not retrieve the invoice details for the ipn ID of ' . $payload->id);
        }

        // Does the status match?
       /* if ($invoice->getStatus() != $payload->status) {
            \Mage::getModel('cryptomarket/method_redirect')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), IPN status and status from CryptoMarket are different. Rejecting this IPN!');
            \Mage::throwException('There was an error processing the IPN - statuses are different. Rejecting this IPN!');
        }*/

        // Does the price match?
        if ($invoice->getPrice() != $payload->price) {
            \Mage::getModel('cryptomarket/method_redirect')>debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), IPN price and invoice price are different. Rejecting this IPN!');
            \Mage::throwException('There was an error processing the IPN - invoice price does not match the IPN price. Rejecting this IPN!');
        }

        // use state as defined by Merchant
        $state = \Mage::getStoreConfig(sprintf('payment/cryptomarket/invoice_%s', $invoice->getStatus()));

        if (false === isset($state) || true === empty($state)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Could not retrieve the defined state parameter to update this order to in the CryptoMarket IPN controller.');
            \Mage::throwException('Could not retrieve the defined state parameter to update this order in the Cryptomarket IPN controller.');
        }

        // Check if status should be updated
        switch ($order->getStatus()) {
            case Mage_Sales_Model_Order::STATE_CANCELED:
            case Mage_Sales_Model_Order::STATUS_FRAUD:
            case Mage_Sales_Model_Order::STATE_CLOSED:
            case Mage_Sales_Model_Order::STATE_COMPLETE:
            case Mage_Sales_Model_Order::STATE_HOLDED:
                // Do not Update
                break;
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
            case Mage_Sales_Model_Order::STATE_PROCESSING:
            default:
                $order->addStatusToHistory(
                    $state,
                    sprintf('[INFO] In Cryptomarket_Core_IpnController::indexAction(), Incoming IPN status "%s" updated order state to "%s"', $invoice->getStatus(), $state)
                )->save();
                break;
        }

        if($payload->status == 'expired')
        {
            $order->cancel();
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancel Transaction.');
            $order->setStatus("canceled");
            $order->save();
        }

        $order_confirmation = \Mage::getStoreConfig('payment/cryptomarket/order_confirmation');
        if($order_confirmation == '1')
        {
            $order->sendNewOrderEmail();
        }

    }
}
