<?php
/**
 * @license Copyright 2016-2018 CryptoMarket Inc., MIT License
 * @see https://github.com/cryptomkt/magento-plugin/blob/master/LICENSE
 */

class Cryptomarket_Core_Block_Iframe extends Mage_Checkout_Block_Onepage_Payment
{
    /**
     */
    protected function _construct()
    {
        $this->setTemplate('cryptomarket/iframe.phtml');

        parent::_construct();
    }

    /**
     * create an invoice and return the url so that iframe.phtml can display it
     *
     * @return string
     */
    public function getPayOrderUrl()
    {

        if (!($quote = Mage::getSingleton('checkout/session')->getQuote()) 
            or !($payment = $quote->getPayment())
            or !($paymentMethod = $payment->getMethod())
            or ($paymentMethod !== 'cryptomarket')
            or (Mage::getStoreConfig('payment/cryptomarket/fullscreen')))
        {
            return 'notcryptomarket';
        }

        \Mage::helper('cryptomarket')->registerAutoloader();

        // fullscreen disabled?
        if (Mage::getStoreConfig('payment/cryptomarket/fullscreen'))
        {
            return 'disabled';
        }

        if (\Mage::getModel('cryptomarket/payorder')->getQuotePaid($this->getQuote()->getId())) {
            return 'paid'; // quote's already paid, so don't show the iframe
        }

        return 'cryptomarket';
    }
}
