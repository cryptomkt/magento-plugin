<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Block_Info extends Mage_Payment_Block_Info
{
    public function _construct()
    {
        $this->setTemplate('cryptomarket/info/default.phtml');
        parent::_construct();
    }

    public function getCryptomarketInvoiceUrl()
    {
        $order       = $this->getInfo()->getOrder();

        if (false === isset($order) || true === empty($order)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Block_Info::getCryptomarketInvoiceUrl(): could not obtain the order.');
            throw new \Exception('In Cryptomarket_Core_Block_Info::getCryptomarketInvoiceUrl(): could not obtain the order.');
        }

        $incrementId = $order->getIncrementId();

        if (false === isset($incrementId) || true === empty($incrementId)) {
            \Mage::helper('cryptomarket')->debugData('[ERROR] In Cryptomarket_Core_Block_Info::getCryptomarketInvoiceUrl(): could not obtain the incrementId.');
            throw new \Exception('In Cryptomarket_Core_Block_Info::getCryptomarketInvoiceUrl(): could not obtain the incrementId.');
        }

        $cryptomarketInvoice = \Mage::getModel('cryptomarket/invoice')->load($incrementId, 'increment_id');

        if (true === isset($cryptomarketInvoice) && false === empty($cryptomarketInvoice)) {
            return $cryptomarketInvoice->getUrl();
        }
    }
}
