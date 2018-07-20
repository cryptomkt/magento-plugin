<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

/**
 */
class Cryptomarket_Core_Model_Invoice extends Mage_Core_Model_Abstract
{
    /**
     */
    protected function _construct()
    {
        $this->_init('cryptomarket/invoice');
    }

    /**
     * Adds data to model based on an Invoice that has been retrieved from
     * CryptoMarket's API
     *
     * @param Cryptomarket\Invoice $invoice
     * @return Cryptomarket_Core_Model_Invoice
     */
    public function prepareWithCryptomarketInvoice($invoice)
    {
        if (false === isset($invoice) || true === empty($invoice)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Model_Invoice::prepareWithCryptomarketInvoice(): Missing or empty $invoice parameter.');
            throw new \Exception('In Cryptomarket_Core_Model_Invoice::prepareWithCryptomarketInvoice(): Missing or empty $invoice parameter.');
        }
        
        $this->addData(
            array(
                'id'               => $invoice->getId(),
                'url'              => $invoice->getUrl(),
                'pos_data'         => $invoice->getPosData(),
                'status'           => $invoice->getStatus(),
                'price'            => $invoice->getPrice(),
                'currency'         => $invoice->getCurrency()->getCode(),
                'order_id'         => $invoice->getOrderId(),
                'invoice_time'     => intval(date_format($invoice->getInvoiceTime(), 'U') / 1000),
                'expiration_time'  => intval(date_format($invoice->getExpirationTime(), 'U') / 1000),
                'current_time'     => intval(date_format($invoice->getCurrentTime(), 'U') / 1000),
                'exception_status' => $invoice->getExceptionStatus(),
                'transactionCurrency' => $invoice->getTransactionCurrency()
            )
        );

        return $this;
    }

    /**
     * Adds information to based on the order object inside magento
     *
     * @param Mage_Sales_Model_Order $order
     * @return Cryptomarket_Core_Model_Invoice
     */
    public function prepareWithOrder($order)
    {
        if (false === isset($order) || true === empty($order)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Model_Invoice::prepateWithOrder(): Missing or empty $order parameter.');
            throw new \Exception('In Cryptomarket_Core_Model_Invoice::prepateWithOrder(): Missing or empty $order parameter.');
        }
        
        $this->addData(
            array(
                'quote_id'     => $order['quote_id'],
                'increment_id' => $order['increment_id'],
            )
        );

        return $this;
    }
}
