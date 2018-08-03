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

        //The Raw Post Data
        $payload = (object) $_POST;

        if (true === empty($payload)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Could not decode the JSON payload from CryptoMarket.');
            error_log('[ERROR] Could not decode the JSON payload from CryptoMarket.');
            throw new \Exception('Could not decode the JSON payload from CryptoMarket.');
        }

        \Mage::helper('cryptomarket')->debugData(array(sprintf('[INFO] In Cryptomarket_Core_IpnController::indexAction(), Incoming IPN message from CryptoMarket: '),$payload,));

        if (true === empty($payload->id) || false === isset($payload->external_id)) {
            \Mage::helper('cryptomarket')->debugData(sprintf('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Did not receive order ID in IPN: ', $payload));
            error_log('[ERROR] Invalid Cryptomarket payment notification message received - did not receive order ID.');
            throw new \Exception('Invalid Cryptomarket payment notification message received - did not receive order ID.');
        }

        if (false === array_key_exists('signature', $payload)) {
            error_log('[Error] Request is not signed:' . var_export($payload, true));
            throw new \Exception('[Error] Request is not signed');
        } else {
            error_log('[Info] Signature present in payload...');
        }

        \Mage::helper('cryptomarket')->registerAutoloader();

        if (\Mage::helper('cryptomarket')->checkResponseSignature($payload->signature, $payload->id, $payload->status) !== true) {
            error_log('[Error] Request is bad signed:' . var_export($payload, true));
            throw new \Exception('[Error] Request is bad signed');
        } else {
            error_log('[Info] Signature valid present in payload...');
        }

        \Mage::helper('cryptomarket')->debugData($payload);

        $order = \Mage::getModel('sales/order')->loadByIncrementId($payload->external_id);

        if (false === isset($order) || true === empty($order)) {
            error_log('[ERROR] Invalid Cryptomarket Callback Received.');
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Invalid Cryptomarket Callback Received.');
            \Mage::throwException('Invalid Cryptomarket Callback Received.');
        }

        $orderId = $order->getIncrementId();
        if (false === isset($orderId) || true === empty($orderId)) {
            error_log('[ERROR] Invalid Cryptomarket Callback ID Received.');
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_IpnController::indexAction(), Invalid Cryptomarket Order ID Callback Received.');
            \Mage::throwException('Invalid Cryptomarket Invalid Cryptomarket Order ID Callback Received.');
        }

        switch ($payload->status) {
            case "-4":
                $this->setCanceledOrder($order, '[INFO] Pago múltiple. Order ID:'.$orderId);

                break;
            case "-3":
                $this->setCanceledOrder($order, '[INFO] Monto pagado no concuerda. Order ID:'.$orderId);

                break;
            case "-2":
                $this->setCanceledOrder($order, '[INFO] Falló conversión. Order ID:'.$orderId);

                break;
            case "-1":
                $this->setCanceledOrder($order, '[INFO] Expiró orden de pago. Order ID:'.$orderId);

                break;
            case "0":
                $this->setProcessingOrder($order, '[INFO] Esperando pago. Order ID:'.$orderId);

                break;
            case "1":
                $this->setProcessingOrder($order, '[INFO] Esperando bloque. Order ID:'.$orderId);

                break;
            case "2":
                $this->setProcessingOrder($order, '[INFO] Esperando procesamiento. Order ID:'.$orderId);

                break;
            case "3":
                $this->setCompletedOrder($order, '[INFO] Pago exitoso. Order ID:'.$orderId);

                break;
            default:
                $this->setCanceledOrder($order, '[INFO] No status payment defined:'.$payload->status.'. Order ID:'.$orderId);

                break;
        }

        $order_confirmation = \Mage::getStoreConfig('payment/cryptomarket/order_confirmation');
        if($order_confirmation == '1')
        {
            $order->sendNewOrderEmail();
        }
    }

    /**
     * setCanceledOrder
     * @param Object $order
     * @param String $log_message
     */
    private function setCanceledOrder($order, $log_message){
        error_log($log_message);
        $order->cancel();
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancel Transaction.');
        $order->setStatus("canceled");
        $order->save();
    }

    /**
     * setProcessingOrder
     * @param Object $order
     * @param String $log_message
     */
    private function setProcessingOrder($order, $log_message){
        error_log($log_message);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Processing Transaction.');
        $order->setStatus("processing");
        $order->save();
    }

    /**
     * setCompletedOrder
     * @param Object $order
     * @param String $log_message
     */
    private function setCompletedOrder($order, $log_message){
        error_log($log_message);
        $order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true, 'Complete Transaction.');
        $order->setStatus("complete");
        $order->save();
    }
}
